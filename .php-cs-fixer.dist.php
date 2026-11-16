<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->append([__DIR__.'/bin/benchmark'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x2Migration:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,
        'void_return' => true,
        'fully_qualified_strict_types' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'phpdoc_line_span' => ['const' => 'multi', 'property' => 'multi', 'method' => 'multi'],
        'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_attributes' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
