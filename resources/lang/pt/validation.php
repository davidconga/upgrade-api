<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'O :attribute deve ser aceite',
    'active_url'           => 'O :attribute não é um URL válido',
    'after'                => 'O :attribute deve ser uma data após :data.',
    'after_or_equal'       => 'O :attribute deve ser uma data após ou igual a :data.',
    'alpha'                => 'O :attribute só pode conter letras.',
    'alpha_dash'           => 'O :attribute só pode conter letras, números e travessões.',
    'alpha_num'            => 'O :attribute só pode conter letras e números.',
    'array'                => 'O :attribute deve ser uma matriz.',
    'before'               => 'O :attribute deve ser uma data antes de :data.',
    'before_or_equal'      => 'O :attribute deve ser uma data anterior ou igual a :data.',
    'between'              => [
        'numeric' => 'O :attribute deve estar entre :min e :max.',
        'file'    => 'O :attribute deve estar entre :min e :max kilobytes.',
        'string'  => 'O :attribute deve estar entre :min e :max caracteres.',
        'array'   => 'O :attribute deve ter entre :min e :max itens.',
    ],
    'boolean'              => 'O :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'O :attributes confirmação de attributes não corresponde.',
    'date'                 => 'O :attribute não é uma data válida.',
    'date_format'          => 'O :attribute não corresponde ao formato :formato',
    'different'            => 'O :attribute e :outros devem ser diferentes.',
    'digits'               => 'O :attribute deve ser :dígitos dígitos.',
    'digits_between'       => 'O :attribute deve estar entre :min e :max dígitos.',
    'dimensions'           => 'O :attribute tem dimensões de imagem inválidas.',
    'distinct'             => 'O :attribute campo de  tem um valor duplicado.',
    'email'                => 'O :attribute deve ser um endereço de correio electrónico válido.',
    'exists'               => 'O seleccionado :attribute é inválido.',
    'file'                 => 'O :attribute deve ser um ficheiro.',
    'filled'               => 'O :attribute campo de é obrigatório.',
    'image'                => 'O :attribute deve ser uma imagem.',
    'in'                   => 'O seleccionado :attribute é inválido.',
    'in_array'             => 'O :attribute campo de não existe em :outro.',
    'integer'              => 'O :attribute deve ser um número inteiro.',
    'ip'                   => 'O :attribute deve ser um endereço IP válido.',
    'json'                 => 'O :attribute deve ser uma cadeia válida do JSON.',
    'max'                  => [
        'numeric' => 'O :attribute não pode ser superior a :max.',
        'file'    => 'O :attribute não pode ser superior a :max kilobytes.',
        'string'  => 'O :attribute não pode ser maior do que :max caracteres.',
        'array'   => 'O :attribute não pode ter mais do que :max itens.',
    ],
    'mimes'                => 'O :attribute não pode ter mais do que :max itens.',
    'mimetypes'            => 'O :attribute deve ser um ficheiro do tipo: :valores..',
    'min'                  => [
        'numeric' => 'O :attribute deve ser pelo menos :min ',
        'file'    => 'O :attribute deve ser pelo menos :min kilobytes.',
        'string'  => 'O :attribute deve ser pelo menos :min  caracteres.',
        'array'   => 'O :attribute deve ser pelo menos :min  items.',
    ],
    'not_in'               => 'O seleccionado :attribute é inválido.',
    'numeric'              => 'O :attribute deve ser um número.',
    'present'              => 'O :attribute campo de attributes deve estar presente.',
    'regex'                => 'O :attribute formato do attribute é inválido.',
    'required'             => 'O :attribute campo de attribute é obrigatório',
    'required_if'          => 'O :attribute campo de attribute é obrigatório quando :outro é :valor.',
    'required_unless'      => 'O :attribute campo de attribute é obrigatório, a menos que :outro esteja em :valores.',
    'required_with'        => 'O :attribute campo de attribute é obrigatório quando :valores estão presentes',
    'required_with_all'    => 'O :attribute campo de attribute é obrigatório quando :valores estão presentes.',
    'required_without'     => 'O :attribute campo de attribute é obrigatório quando :valores não estão presentes.',
    'required_without_all' => 'O :attribute campo de attribute é necessário quando nenhum dos :valores está presente.',
    'same'                 => 'O :attribute e :outros devem corresponder.',
    'size'                 => [
        'numeric' => 'O :attribute deve ser :tamanho.',
        'file'    => 'O :attribute deve ser :tamanho kilobytes.',
        'string'  => 'O :attribute deve ser :tamanho caracteres.',
        'array'   => 'O :attribute deve conter: tamanho dos artigos.',
    ],
    'string'               => 'O :attribute deve ser uma corda.',
    'timezone'             => 'O :deve ser uma zona válida.',
    'unique'               => 'O :attribute já foi tomado.',
    'uploaded'             => 'O :attribute não conseguiu carregar.',
    'url'                  => 'O :formato do attribute é inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        's_latitude' => [
            'required' => 'Endereço de origem obrigatório',
        ],
        'd_latitude' => [
            'required' => 'Endereço de destino obrigatório',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | O following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
