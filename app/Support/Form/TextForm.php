<?php

namespace App\Support\Form;

class TextForm implements IForm
{
    public static function render($name, $field, $data, $params)
    {
        return view('support.form.input', [
            'name'  => $name,
            'type'  => 'text',
            'data'  => $data,
            'field' => $field,
        ]);
    }
}
