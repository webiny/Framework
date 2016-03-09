Validation Component
=====================

## Using validation
Use `ValidationTrait` to access the component. The component does not require any configuration.
You can validate agains single validator or pass an array of validators, or event a comma-separated string of validators:

```php
class MyClass {
    use ValidationTrait;
    
    public function example() {
        try{
            $this->validation()->validate(123, 'number');
            $this->validation()->validate('my@email.com', 'email');
            $this->validation()->validate(20, ['number','gte:100']);
            $this->validation()->validate(10, 'number,lt:50']);
        } catch (ValidationException $e) {
            // Handle exception
        }
        
        // You can also tell validation to NOT THROW but simply return `false`
        // NOTE: make sure you use `===` because if value is invalid we return an error message!
        if($this->validation()->validate(123, 'number', false) === true) {
            // I am a valid number
        } else {
            // I am an invalid number
        }
    }
}
```

## Adding custom validators
You can add/override validators by implementing a `ValidatorInterface`:

```php
class MyValidator implements ValidatorInterface {
    
    public function getName() {
        return 'myCustomValidator';
    }
    
    public function validate($value, $params = [], $throw = true) {
        $message = 'Value must be this and that';
        
        $myExpectedValue = 'myExpectedValue';
        
        if ($value == $myExpectedValue) {
            return true;
        }
        
        if ($throw) {
            throw new ValidationException($message);
        }
        
        return $message;
    }
}

// Add new validator
$this->validation()->addValidator(new MyValidator());

// And now call your validator
$this->validation()->validate(123, 'myCustomValidator');
```

You can also register your validators through services, by tagging them with `validation-plugin`. 

```yaml
Services:
    MyCustomValidator:
        Class: \My\Custom\Validator
        Tags: ['validation-plugin']
```

## Validator parameters
If you pass params to your validator, like `$this->validation()->validate(123, 'myCustomValidator:50:20');` they will be passed to your 
validator class in `$params`. `$params` are index based, so your parameters will look like `[50, 20]`.