<?php

namespace TNatanael\BrFormatter;

use Carbon\Carbon;

class FormatManipulator
{
    /**
     * Função format_input aplica as funções de formatação a uma instância de Laravel Model com destino ao DB
     *
     * @param Model $model Objeto de banco de dados
     *
     * @return Model Objeto $model com formatação aplicada nos fields para o padrão BR
     */
    public static function format_input($model)
    {
        return self::format_loop($model, true);
    }

    /**
     * Função format_output aplica funções de formatação a uma instância de Laravel Model com destino ao Usuário
     *
     * @param Model $model Objeto de banco de dados
     *
     * @return Model Objeto $model com formatação aplicada nos fields para o padrão DB
     */
    public static function format_output($model)
    {
        return self::format_loop($model, false);
    }

    /**
     * Função format_loop auxiliar para as duas funções de formatação de model
     *
     * @param Model $model Objeto de banco de dados
     * @param String $in_out Define o tipo de iteração, input ou output
     *
     * @return Model Objeto $model com formatação aplicada nos fields de acordo com a variável $input
     */
    private static function format_loop($model, $input)
    {
        //Se estivermos salvando pegamos os atributos modificados, senão percorremos todos os atributos
        if ($input) {
            $attributes = $model->getDirty();
        } else {
            $attributes = $model->getAttributes();
        }
        
        //Percorre os atributos alterados
        foreach ($attributes as $attribute => $value) {
            //Datetime
            if (isset($model->datetime_attributes) && in_array($attribute, $model->datetime_attributes)) {
                $model[$attribute] = ($input) ? self::br_to_datetime($model[$attribute]) : self::datetime_to_br($model[$attribute]);
            }
            //Date
            if (isset($model->date_attributes) && in_array($attribute, $model->date_attributes)) {
                $model[$attribute] = ($input) ? self::br_to_date($model[$attribute]) : self::date_to_br($model[$attribute]);
            }
            //Money
            if (isset($model->money_attributes) && in_array($attribute, $model->money_attributes)) {
                $model[$attribute] = ($input) ? self::br_to_money($model[$attribute]) : self::money_to_br($model[$attribute]);
            }
            //Numeric
            if (isset($model->numeric_attributes) && in_array($attribute, $model->numeric_attributes)) {
                $model[$attribute] = ($input) ? self::br_to_numeric($model[$attribute]) : self::numeric_to_br($model[$attribute]);
            }
            //Boolean
            if (isset($model->boolean_attributes) && in_array($attribute, $model->boolean_attributes)) {
                $model[$attribute] = ($input) ? self::any_to_boolean($model[$attribute]) : self::boolean_to_br($model[$attribute]);
            }
        }

        return $model;
    }

    /**
     * Função date_to_br formata uma string de data no padrão Y-m-d (DB Default) para o padrão BR d/m/Y
     * OBS: este tipo de data (sem horário, tipo DATE no MySql) não pode entrar no array $dates do Laravel
     * porisso precisamos fazer o parse do field no DB como se fosse uma string.
     *
     * @param String $date Um objeto de data do tipo Carbon
     *
     * @return String Retorna a string de data formatada
     */
    public static function date_to_br($date)
    {
        return (is_string($date)) ? Carbon::parse($date)->format('d/m/Y') : '00/00/0000';
    }

    /**
     * Função br_to_date cria um objeto Carbon a partir de uma string de data no formato BR d/m/Y
     *
     * @param String $string Uma string contendo uma data no formato d/m/Y
     *
     * @return Carbon Objeto Carbon
     */
    public static function br_to_date($string)
    {
        return (is_string($string)) ? Carbon::createFromFormat('d/m/Y', $string)->startOfDay() : null;
    }

    /**
     * Função timestamp_to_br formata uma string timestamp no padrão Y-m-d H:i:s (DB Default) para o padrão BR d/m/Y H:i (sem os segundos)
     *
     * @param String|Carbon $timestamp String contendo uma data no padrão americano ou objeto de data do tipo Carbon
     *
     * @return String Retorna a string de datetime formatada
     */
    public static function datetime_to_br($timestamp)
    {
        //TODO: Analizar esta implementação parece desnecessária, se o DB sempre retorna um mesmo tipo de dados, não precisamos checar ou converter...
        if (is_object($timestamp)) {
            //Tipo Carbon Date
            return $timestamp->toDateTimeString()->format('d/m/Y H:i');
        }
        if (is_string($timestamp)) {
            //String de Data
            return Carbon::createFromFormat('Y-m-d H:i:s', $timestamp)->format('d/m/Y H:i');
        }
        return '00/00/0000 00:00';
    }

    /**
     * Função br_to_date cria um objeto Carbon a partir de uma string de datetime no formato BR d/m/Y H:i:s
     *
     * @param String $string String contendo uma datetime no padrão BR
     *
     * @return Carbon Objeto Carbon
     */
    public static function br_to_datetime($string)
    {
        if (!is_string($string)) null;

        //Se foi informado um objeto do tipo Carbon sómente retornamos para o DB
        if (is_object($string) && $string instanceof Carbon) {
            return $string;
        }
        //Inserção de segundos é opcional
        if (substr_count($string, ':') == 1) {
            return (is_string($string)) ? Carbon::createFromFormat('d/m/Y H:i', $string) : null;
        }

        //Para string fazemos o parse para um Carbon object
        return (is_string($string)) ? Carbon::createFromFormat('d/m/Y H:i:s', $string) : null;
    }

    /**
     * Função br_to_date cria um objeto Carbon a partir de uma string de data no formato BR d/m/Y
     *
     * @param Numeric $numeric Valor numérico retornado pelo DB
     *
     * @return String String monetária formatada para padrão BR
     */
    public static function money_to_br($numeric)
    {
        //Retorna 0 formatado caso inválido
        return (is_numeric($numeric)) ? 'R$ '. self::numeric_to_br($numeric) : 'R$ 0,00';
    }

    /**
     * Função br_to_money converte uma string contendo um valor separado pos virgula para um numeric
     *
     * @param String $string String contendo um valor inteiro ou numérico separado por virgula
     *
     * @return Numeric Um valor do tipo numéric padrão para salvar no DB
     */
    public static function br_to_money($string)
    {
        if (str_contains($string, ',')) {
            //Remove o prefixo de REAL caso exista
            $string = str_replace('R$', '', $string);
            //Remove os pontos de milhar, caso existam
            $string = str_replace('.', '', $string);
            //Troca a virgula por pontos
            $string = str_replace(',', '.', $string);
            //Retorna
            return $string;
        } elseif (is_numeric($string)) {
            return $string;
        } else {
            //Retorna 0 caso inválido!
            return 0;
        }
    }

    /**
     * Função br_to_numeric converte uma string contendo um valor numérico no padrão BR (com virgula) para o DB (com ponto)
     *
     * @param String $string String contendo um valor inteiro ou numérico
     *
     * @return Numeric Valor numérico monetária formatada para padrão DB
     */
    public static function br_to_numeric($string)
    {
        if (str_contains($string, ',')) {
            //Remove os pontos
            $string = str_replace('.', '', $string);
            //Troca a virgula por pontos
            $string = str_replace(',', '.', $string);
            //Retorna
            return $string;
        } elseif (is_numeric($string)) {
            return $string;
        } else {
            //Retorna 0 caso inválido!
            return 0;
        }
    }

    /**
     * Função numeric_to_br converte um valor numérico no padrão DB (com ponto) para o padrão BR (com virgula)
     *
     * @param Numeric $numeric Valor retornado do DB
     *
     * @return String String numérica formatada para padrão BR
     */
    public static function numeric_to_br($numeric)
    {
        if (is_numeric($numeric)) {
            return number_format($numeric, 2, ',', '.');
        } else {
            //Retorna 0 formatado caso inválido
            return '0,00';
        }
    }

    /**
     * Função any_to_numeric converte uma string contendo um valor conversível para booleano no padrão BR (Sim ou Não, 0 ou 1, true ou false) para o DB (0 ou 1)
     *
     * @param String $string String contendo um valor booleano
     *
     * @return Boolean Valor booleano formatado para padrão DB
     */
    public static function any_to_boolean($string)
    {
        if ($string === 'Sim'
            ||
            $string === 1
            ||
            $string === 'true'
            ||
            $string === true
        ) {
            return 1;
        }
        //Retorna 0 por default, ou caso seja inválido
        return 0;
    }

    /**
     * Função boolean_to_br converte um valor booleano no padrão DB (1 ou 0) para o padrão BR (Sim ou Não)
     *
     * @param Boolean $boolean Valor retornado do DB
     *
     * @return String String formatada para padrão BR
     */
    public static function boolean_to_br($boolean)
    {
        if ($boolean == 1) {
            return 'Sim';
        }
        //Se não tem um valor booleano armazenado retornamos falso, por padrão
        return 'Não';
    }
}
