<?php

declare(strict_types=1);
$finder = PhpCsFixer\Finder::create()
    ->in(array(
        __DIR__ . '/adresse',
    ))
        ;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRules(array(
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        'concat_space' => array('spacing' => 'one'),
        'array_syntax' => array('syntax' => 'long'),
        'method_argument_space' => array('on_multiline' => 'ensure_fully_multiline'),
        'new_with_braces' => true,
        'no_whitespace_in_blank_line' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_useless_return' => true,
        'no_unneeded_final_method' => true,
        'no_unset_cast' => false,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_extra_blank_lines' => true,
        'no_empty_statement' => true,
        'no_empty_comment' => true,
        'object_operator_without_whitespace' => true,
        'ordered_class_elements' => false,
        'phpdoc_var_without_name' => true,
        'phpdoc_types' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'protected_to_private' => false,
        'semicolon_after_instruction' => true,
        'short_scalar_cast' => true,
        'simplified_null_return' => false,
        'simple_to_complex_string_variable' => false,
        'standardize_not_equals' => true,
        'standardize_increment' => true,
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => array(
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
            'less_and_greater' => null,
        ),
    ))
    ->setFinder($finder)
;

return $config;
