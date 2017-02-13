<?php
/**
 * Text input field with validation for numeric values.
 * 
 * @package forms
 * @subpackage fields-formattedinput
 */
class QuantityField extends NumericField
{

    public function Type()
    {
        return 'quantity numeric text';
    }

    /** PHP Validation **/
    public function validate($validator)
    {
        if ($this->value && !is_numeric(trim($this->value))) {
            $validator->validationError(
                $this->name,
                _t(
                    'NumericField.VALIDATION', "'{value}' is not a number, only numbers can be accepted for this field",
                    array('value' => $this->value)
                ),
                "validation"
            );
            return false;
        } elseif (!$this->value) {
            $validator->validationError(
                $this->name,
                sprintf(_t('Form.FIELDISREQUIRED', '%s is required'), $this->title),
                "validation"
            );
            return false;
        } else {
            return true;
        }
    }
    
    public function dataValue()
    {
        return (is_numeric($this->value)) ? $this->value : 0;
    }
}
