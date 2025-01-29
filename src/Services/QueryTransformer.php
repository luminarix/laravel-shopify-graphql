<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
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

        $fragments = [];
        foreach ($ast->definitions as $definition) {
            if ($definition instanceof FragmentDefinitionNode) {
                $fragments[$definition->name->value] = $definition;
            }
        }

        $applyPaginationRecursively = static function (?SelectionSetNode $selectionSet, array $paginationConfig, array $pathStack = []) use (&$applyPaginationRecursively, &$fragments) {
            if ($selectionSet === null) {
                return;
            }

            foreach ($selectionSet->selections as $selection) {
                if ($selection instanceof FieldNode) {
                    $currentFieldName = $selection->name->value;
                    $currentPath = array_merge($pathStack, [$currentFieldName]);
                    $pathString = implode('.', $currentPath);

                    if (array_key_exists($pathString, $paginationConfig)) {
                        $selection->arguments = self::applyPaginationArguments($selection->arguments, $paginationConfig[$pathString]);
                    }

                    $applyPaginationRecursively($selection->selectionSet, $paginationConfig, $currentPath);
                } elseif ($selection instanceof FragmentSpreadNode) {
                    $fragmentName = $selection->name->value;
                    if (isset($fragments[$fragmentName])) {
                        $applyPaginationRecursively($fragments[$fragmentName]->selectionSet, $paginationConfig, $pathStack);
                    }
                } elseif ($selection instanceof InlineFragmentNode) {
                    $applyPaginationRecursively($selection->selectionSet, $paginationConfig, $pathStack);
                }
            }
        };

        foreach ($ast->definitions as $definition) {
            if ($definition instanceof OperationDefinitionNode) {
                $applyPaginationRecursively($definition->selectionSet, $paginationConfig);
            }
        }

        return Printer::doPrint($ast);
    }

    private static function applyPaginationArguments(NodeList $arguments, array $paginationArgs): NodeList
    {
        /** @var Node[] $argsArray */
        $argsArray = iterator_to_array($arguments);
        $argsMap = [];

        foreach ($argsArray as $argNode) {
            // @phpstan-ignore property.notFound
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
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            if (Arr::isAssoc($value)) {
                $fields = [];
                foreach ($value as $k => $v) {
                    $fields[] = $k . ': ' . self::phpValueToGraphQLLiteral($v);
                }

                return '{' . implode(', ', $fields) . '}';
            }

            $items = array_map(static fn ($item) => self::phpValueToGraphQLLiteral($item), $value);

            return '[' . implode(', ', $items) . ']';
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
