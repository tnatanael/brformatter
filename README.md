
# tnatanael\brformatter

Traduz automaticamente no backend, valores no padrão BR pra o DB e vice-versa.

**Requisitos:**
Laravel 5.6
Laravel 8

**Instalação via composer:**

    composer require tnatanael/brformatter


**Utilização**
Utilize a seguinte sintaxe nos seus models

    
    namespace App;
    
    use TNatanael\BrFormatter\BaseModel;
    
    class Product extends BaseModel
    {
        //Date Attributes
        public $date_attributes = [
            'date'
        ];
    
        //Datetime Attributes
        public $datetime_attributes = [
            'datetime',
        ];
    
        //Money Attributes
        public $money_attributes = [
            'money',
        ];
    
        //Numeric Attributes
        public $numeric_attributes = [
            'numeric'
        ];
    
        //Boolean Attributes
        public $boolean_attributes = [
            'boolean',
        ];
    }

Para retornar os valores exatamente como salvos no db utilize:

    $product->getOriginal();
