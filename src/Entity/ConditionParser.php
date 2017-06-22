<?php

namespace App\Entity;

class ConditionParser
{
    /**
     * @param string $expression
     * @return array
     */
    public function parse($expression)
    {
        $conditionString = $expression;
        $result = $this->splitConditions($conditionString);
        if ($this->isXor($result)) {
            $opositResult = $this->splitConditions($conditionString, true);
            $result = $this->rebuildAsXor($result, $opositResult);
        }
        return $result;
    }
    /**
     * @param string $conditionQuery
     * @param bool   $doOposit
     * @return array
     */
    private function splitConditions($conditionQuery, $doOposit = false)
    {
        $result = [];
        if (strpos($conditionQuery, ' XOR ') !== false) {
            foreach (explode(' XOR ', $conditionQuery) as $xorSubcondition) {
                $result['XOR'][] = $this->splitConditions($xorSubcondition, $doOposit);
            }
        } elseif (strpos($conditionQuery, ' OR ') !== false) {
            foreach (explode(' OR ', $conditionQuery) as $orSubcondition) {
                $result['$or'][] = $this->splitConditions($orSubcondition, $doOposit);
            }
        } elseif (strpos($conditionQuery, ' AND ') !== false) {
            foreach (explode(' AND ', $conditionQuery) as $andSubcondition) {
                $splited = $this->splitConditions($andSubcondition, $doOposit);
                $field = array_keys($splited)[0];
                $result[$field] = $splited[$field];
            }
        } else {
            $result = $this->splitSubCondition($conditionQuery, $doOposit);
        }
        return $result;
    }
    /**
     * @param string $subcondition
     * @param bool $oposit
     * @return array
     */
    private function splitSubCondition($subcondition, $oposit)
    {
        $result = [];
        foreach ($this->getOperationsMapping() as $mongoOperator => $sqlOperator) {
            if (strpos($subcondition, " {$sqlOperator} ") !== false) {
                list($field, $value) = explode(" {$sqlOperator} ", $subcondition);
                $field = trim($field, "'\"`()");
                $value = trim($value, "'\"`()");
                if (is_numeric($value)) {
                    $value = $value * 1;
                }
                if ($oposit) {
                    if ($mongoOperator == '$eq') {
                        $result[$field]['$ne'] = $value;
                    } elseif ($mongoOperator == '$ne') {
                        $result[$field] = $value;
                    } else {
                        $result[$field]['$not'][$mongoOperator] = $value;
                    }
                } else {
                    if ($mongoOperator == '$eq') {
                        $result[$field] = $value;
                    } else {
                        $result[$field][$mongoOperator] = $value;
                    }
                }
            }
        }
        return $result;
    }
    /**
     * @return array
     */
    private function getOperationsMapping()
    {
        return [
            '$lte' => '<=',
            '$gte' => '>=',
            '$ne' => '<>',
            '$eq' => '=',
            '$lt' => '<',
            '$gt' => '>',
        ];
    }
    /**
     * @param array $result
     * @return bool
     */
    private function isXor($result)
    {
        return array_key_exists('XOR', $result);
    }
    /**
     * @param array $straightResult
     * @param array $opositResult
     * @return array
     */
    private function rebuildAsXor($straightResult, $opositResult)
    {
        $xorResult = [];
        $elementsCount = count($straightResult['XOR']);
        for ($i = 0; $i < $elementsCount; $i++) {
            $preparedXOR = $straightResult['XOR'];
            $preparedXOR[$i] = $opositResult['XOR'][$i];
            $inversedCondition = [];
            foreach ($preparedXOR as $condition) {
                $inversedCondition = array_merge($inversedCondition, $condition);
            }
            $xorResult['$or'][$i] = $inversedCondition;
        }
        return $xorResult;
    }
}
