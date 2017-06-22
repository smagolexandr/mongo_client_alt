<?php

namespace App\Entity;

/**
 * Class Query
 * @SuppressWarnings(PHPMD)
 */
class Query
{
    /**
     * @const Array
     */
    const OPERATORS = ['select', 'from', 'where', 'order by', 'limit', 'offset'];

    /**
     * Input sql query as string
     *
     * @var string
     */
    private $query;

    /**
     * Selected projections
     *
     * @var array
     */
    private $projections;

    /**
     * Table name
     *
     * @var string
     */
    private $table;

    /**
     * @var int
     */
    private $limit;

    /**
     * Array formatted to mosql
     *
     * @var array
     */
    private $where;

    /**
     * @var int
     */
    private $offset;

    /**
     * Array in format [ 'field' => 'ASC' ]
     *
     * @var array
     */
    private $order;


    /**
     * Query constructor.
     *
     * @param $exp string SQL expression
     */
    public function __construct($exp)
    {
        $this->query = strtolower($exp);

        foreach ($this::OPERATORS as $operator) {
            if (strpos($this->query, $operator) !== false) {
                $operators[$operator] = strpos($this->query, $operator);
            }
        }

        asort($operators);

        foreach ($operators as $operator => $start) {
            $next = next($operators);
            switch ($operator) {
                case "select":
                    $projectionsString = $this->getOperatorContent($this->query, $operator, $start, $next);
                    if ($projectionsString == "*") {
                        $this->projections = null;
                    } else {
                        $this->projections = array_fill_keys(
                            array_map(
                                'trim',
                                explode(
                                    ",",
                                    $projectionsString
                                )
                            ),
                            1
                        );
                        if (strpos($this->query, "_id")) {
                            $projections["_id"] = 1;
                        } else {
                            $projections["_id"] = 0;
                        }
                    }
                    break;

                case "from":
                    $this->table = $this->getOperatorContent($this->query, $operator, $start, $next);

                    break;

                case "limit":
                    $this->limit = intval($this->getOperatorContent($this->query, $operator, $start, $next));
                    break;

                case "offset":
                    $this->offset = intval($this->getOperatorContent($this->query, $operator, $start, $next));
                    break;

                case "where":
                    $whereStr = $this->getOperatorContent($exp, $operator, $start, $next);
                    $parser = new ConditionParser();
                    $this->where = $parser->parse($whereStr);
                    break;

                case "order by":
                    $exp = explode(' ', $this->_getOperatorContent($exp, $operator, $start, $next));

                    if (count($exp) != 2) {
                        return false;
                    }
                    $orderField = $exp[0];
                    $orderDirection = strtoupper($exp[1]);

                    if (!in_array($orderDirection, ['ASC', 'DESC'])) {
                        return false;
                    }
                    $this->order = [$orderField => ($orderDirection == 'ASC' ? 1 : -1)];
            }
        }

        return $this;
    }

    /**
     * instance of DB
     *
     * @param  $mongodb object
     * @return array
     */
    public function execute($mongodb)
    {
        if (isset($this->table)) {
            $collection = $mongodb->test->{$this->table};
        }

        $items = [];

        $cursor = $collection->find(
            isset($this->where) ? $this->where : [],
            ['projection' => $this->projections,
                "limit" => isset($this->limit) ? $this->limit : null,
                "skip" => isset($this->offset) ? $this->offset : null,
                "sort" => isset($this->order) ? $this->order : null,
            ]
        );

        foreach ($cursor as $document) {
            array_push($items, $document);
        }

        return $items;
    }

    /**
     * Returns string with content between 2 operators
     *
     * @param string $exp SQL expression
     * @param string $operator name of current operator
     * @param int $start start position of current operator
     * @param int $next start position of next operator
     *
     * @return string
     */
    private function getOperatorContent($exp, $operator, $start, $next)
    {
        return trim(
            substr(
                $exp,
                $start + strlen($operator),
                $next == false ? strlen($exp) : $next - ($start + strlen($operator))
            )
        );
    }

    /**
     * @return mixed
     */
    public function getProjections()
    {
        return $this->projections;
    }

    /**
     * @param mixed $projections
     */
    public function setProjections($projections)
    {
        $this->projections = $projections;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     */
    public function setWhere($where)
    {
        $this->where = $where;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
}
