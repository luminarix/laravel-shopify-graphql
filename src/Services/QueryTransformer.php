<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
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

        if (!isset($ast->definitions[0]) || !property_exists($ast->definitions[0], 'selectionSet')) {
            return $queryString;
        }

        $applyPaginationRecursively = static function (?SelectionSetNode $selectionSet, array $paginationConfig, array $pathStack = []) use (&$applyPaginationRecursively) {
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
                }
            }
        };

        $applyPaginationRecursively($ast->definitions[0]->selectionSet, $paginationConfig);

        return Printer::doPrint($ast);
    }

    private static function applyPaginationArguments(NodeList $arguments, array $paginationArgs): NodeList
    {
        $argsArray = iterator_to_array($arguments);
        $argsMap = [];

        foreach ($argsArray as $argNode) {
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

        if (is_string($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
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

        return json_encode((string)$value, JSON_UNESCAPED_UNICODE);
    }
}
