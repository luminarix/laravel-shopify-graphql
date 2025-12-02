<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use Illuminate\Support\Arr;
use Luminarix\Shopify\GraphQLClient\Contracts\QueryTransformable;

class QueryTransformer implements QueryTransformable
{
    public static function transformQueryWithPagination(string $queryString, array $paginationConfig): string
    {
        if (empty($paginationConfig)) {
            return $queryString;
        }

        $ast = Parser::parse($queryString);
        $fragments = self::extractFragments($ast);

        foreach ($ast->definitions as $definition) {
            if ($definition instanceof OperationDefinitionNode) {
                self::applyPaginationToSelectionSet(
                    $definition->selectionSet,
                    $paginationConfig,
                    $fragments
                );
            }
        }

        return Printer::doPrint($ast);
    }

    /**
     * @return array<string, FragmentDefinitionNode>
     */
    private static function extractFragments(DocumentNode $ast): array
    {
        $fragments = [];

        foreach ($ast->definitions as $definition) {
            if ($definition instanceof FragmentDefinitionNode) {
                $fragments[$definition->name->value] = $definition;
            }
        }

        return $fragments;
    }

    /**
     * @param  array<string, array<string, mixed>>  $paginationConfig
     * @param  array<string, FragmentDefinitionNode>  $fragments
     * @param  array<int, string>  $pathStack
     */
    private static function applyPaginationToSelectionSet(
        ?SelectionSetNode $selectionSet,
        array $paginationConfig,
        array $fragments,
        array $pathStack = []
    ): void {
        if ($selectionSet === null) {
            return;
        }

        foreach ($selectionSet->selections as $selection) {
            if ($selection instanceof FieldNode) {
                self::processFieldNode($selection, $paginationConfig, $fragments, $pathStack);
            } elseif ($selection instanceof FragmentSpreadNode) {
                self::processFragmentSpread($selection, $paginationConfig, $fragments, $pathStack);
            } elseif ($selection instanceof InlineFragmentNode) {
                self::applyPaginationToSelectionSet($selection->selectionSet, $paginationConfig, $fragments, $pathStack);
            }
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $paginationConfig
     * @param  array<string, FragmentDefinitionNode>  $fragments
     * @param  array<int, string>  $pathStack
     */
    private static function processFieldNode(
        FieldNode $field,
        array $paginationConfig,
        array $fragments,
        array $pathStack
    ): void {
        $currentPath = [...$pathStack, $field->name->value];
        $pathString = implode('.', $currentPath);

        if (array_key_exists($pathString, $paginationConfig)) {
            $field->arguments = self::applyPaginationArguments($field->arguments, $paginationConfig[$pathString]);
        }

        self::applyPaginationToSelectionSet($field->selectionSet, $paginationConfig, $fragments, $currentPath);
    }

    /**
     * @param  array<string, array<string, mixed>>  $paginationConfig
     * @param  array<string, FragmentDefinitionNode>  $fragments
     * @param  array<int, string>  $pathStack
     */
    private static function processFragmentSpread(
        FragmentSpreadNode $spread,
        array $paginationConfig,
        array $fragments,
        array $pathStack
    ): void {
        $fragmentName = $spread->name->value;

        if (isset($fragments[$fragmentName])) {
            self::applyPaginationToSelectionSet(
                $fragments[$fragmentName]->selectionSet,
                $paginationConfig,
                $fragments,
                $pathStack
            );
        }
    }

    /**
     * @param  NodeList<ArgumentNode>  $arguments
     * @param  array<string, mixed>  $paginationArgs
     * @return NodeList<ArgumentNode>
     */
    private static function applyPaginationArguments(NodeList $arguments, array $paginationArgs): NodeList
    {
        $argsMap = [];

        foreach ($arguments as $argNode) {
            $argsMap[$argNode->name->value] = $argNode;
        }

        foreach ($paginationArgs as $argKey => $argValue) {
            $argsMap[$argKey] = new ArgumentNode([
                'name' => new NameNode(['value' => $argKey]),
                'value' => Parser::parseValue(self::phpValueToGraphQLLiteral($argValue)),
            ]);
        }

        return new NodeList(array_values($argsMap));
    }

    private static function phpValueToGraphQLLiteral(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => (string)$value,
            is_array($value) => self::arrayToGraphQLLiteral($value),
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        };
    }

    private static function arrayToGraphQLLiteral(array $value): string
    {
        if (Arr::isAssoc($value)) {
            $fields = array_map(
                static fn (string $k, mixed $v): string => "{$k}: " . self::phpValueToGraphQLLiteral($v),
                array_keys($value),
                array_values($value)
            );

            return '{' . implode(', ', $fields) . '}';
        }

        $items = array_map(
            static fn (mixed $item): string => self::phpValueToGraphQLLiteral($item),
            $value
        );

        return '[' . implode(', ', $items) . ']';
    }
}
