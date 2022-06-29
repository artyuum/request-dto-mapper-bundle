<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                               => true,
        'array_indentation'                      => true,
        'method_chaining_indentation'            => true,
        'no_useless_else'                        => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'global_namespace_import'                => true,
        'braces'                                 => true,
        'indentation_type'                       => true,
        'binary_operator_spaces'                 => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'yoda_style'                             => [
            'equal'            => false,
            'identical'        => false,
            'less_and_greater' => false,
        ],
        'concat_space'                           => [
            'spacing' => 'one',
        ],
        'no_unreachable_default_argument_value'  => true,
        'no_useless_return'                      => true,
        'php_unit_strict'                        => true,
        'phpdoc_order'                           => true,
        'strict_comparison'                      => true,
        'strict_param'                           => true,
    ])
    ->setIndent(str_pad('', 4))
    ->setFinder(
        $finder
    );
