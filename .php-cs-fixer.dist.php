<?php

$rules = [
    '@PSR2' => true,
    'array_syntax' => ['syntax' => 'short'],
    'trim_array_spaces' => true,
    'no_useless_else' => true,
    'strict_param' => true,
    'final_class' => false,
    'no_unused_imports' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['class', 'const', 'function'],
    ],
    'single_quote' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => [
        'after_heredoc' => true,
        'elements' => [
            'arguments',
            'arrays',
        ],
    ],
    'class_definition' => [
        'space_before_parenthesis' => true,
    ],
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_indent' => true,
    'phpdoc_no_package' => true,
    'phpdoc_order' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_trim' => true,
    'phpdoc_var_without_name' => true,
];

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(['src', 'tests'])
            # https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/4348
            ->exclude(['cache']),
    );
