<?php

namespace Core\Supports;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestRequest
{
    /**
     * The current query value bindings.
     *
     * @var array
     */
    protected $bindings = [
        'fields'     => null,
        'embeds'     => null,
        'perpage'    => null,
        'page'       => null,
        'orderby'    => null,
        'conditions' => null
    ];

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $fields;

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $embeds;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $perpage;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $page;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orderby;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $conditions;

    /**
     * @var array
     */
    protected $search_fields = [];

    /**
     * @var string
     */
    protected $search_with = 'icontains';

    /**
     * @var string
     */
    protected $default_order = 'id';

    /**
     * @var string
     */
    protected $sort_order = 'desc';

    /**
     * Json array binding compiled.
     *
     * @var string
     */
    protected $compiled_binding;

    /**
     * RepositoryRequest constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getFromRequest()
    {
        // set order
        $orders = $this->input('sorting');
        if (!$orders && !is_array($orders)) {
            $orders = [$this->default_order => $this->sort_order];
        }
        $this->order($orders);

        // set limit
        $limit         = $this->input('count') ?: $this->perpage;
        $limit         = ($limit == 'infinity') ? 100000 : $limit;
        $this->perpage = (int) $limit;

        // set offset
        $this->page = $this->input('page');

        // set search
        $terms = $this->input('search', false);
        if ($terms && count($this->search_fields) > 0) {
            foreach ($this->search_fields as $field) {
                $data['Or.' . $field . '.' . $this->search_with] = $terms;
            }

            $this->where($data);
        }

        return $this;
    }

    /**
     * Set field repository that will be search to.
     *
     * @param array $field
     * @return void
     */
    public function searchField($field = [])
    {
        $field = (!is_array($field)) ? [$field] : $field;

        $this->search_fields = $field;

        return $this;
    }

    /**
     * set parameters can be filled with :
     *
     * icontains
     * WHERE name LIKE '%slene%'
     * Case insensitive, will match any name that contains 'slene'
     *
     * contains
     * WHERE name LIKE BINARY '%slene%'
     * Case sensitive, only match name that contains 'slene'
     *
     * startswith
     * WHERE name LIKE BINARY 'slene%'
     * Case sensitive, only match name that starts with 'slene'
     *
     * istartswith
     * WHERE name LIKE 'slene%'
     * Case insensitive, will match any name that starts with 'slene'
     *
     * endswith
     * WHERE name LIKE BINARY '%slene'
     * Case sensitive, only match name that ends with 'slene'
     *
     * iendswith
     * WHERE name LIKE '%slene'
     * Case insensitive, will match any name that ends with 'slene'
     *
     * @param $parameters
     * @return $this->search_with
     */
    public function setSearchWith($parameters)
    {
        $this->search_with = $parameters;

        return $this->search_with;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string            $key
     * @param  string|array|null $default
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }

    /**
     * Get offset based on request queries.
     *
     * @param  $limit
     * @return int
     */
    protected function getOffset($limit)
    {
        $offset = ($this->input('page')) ? ($this->input('page') - 1) * $limit : 0;

        return $offset;
    }

    /**
     * Set the columns to be selected.
     * select called just onces on instances.
     *
     * @param  array|string $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->fields = is_array($fields) ? $fields : func_get_args();

        return $this;
    }

    /**
     * Set columns to be joined.
     * make it false if nothing should be joined.
     *
     * @param string|array|boolean $fields
     * @return $this
     */
    public function join($fields)
    {
        if ($fields) {
            $fields       = is_array($fields) ? $fields : func_get_args();
            $this->embeds = is_array($this->embeds) ? array_keys(array_flip(array_merge($this->embeds, $fields))) : $fields;
        } else {
            $this->embeds = 'none';
        }

        return $this;
    }

    /**
     * Chain to set Order by fields and sort.
     *
     * @param array $sorts [ fields => 'asc|desc' ]
     * @return $this
     */
    public function order(array $sorts)
    {
        foreach ($sorts as $sort => $order) {
            $prefix = "";
            if ($order == "desc") {
                $prefix = "-";
            }

            $sorting       = $prefix . $sort;
            $this->orderby = is_array($this->orderby) ? array_merge($sorting, $this->orderby) : $sorting;
        }
        
        // implement new orderby query string
        if ($orderby = $this->input('orderby')) {
            $this->orderby = is_array($this->orderby) ? array_merge($orderby, $this->orderby) : $orderby;
        }

        return $this;
    }

    /**
     * Specify multiple values in a WHERE clause.
     *
     * @param string $field
     * @param array  $values
     * @return $this
     */
    public function whereIn($field, array $values)
    {
        $value = implode('.', $values);

        return $this->where($field, $value, 'in');
    }

    /**
     * Specify except values in a WHERE clause.
     *
     * @param string $field
     * @param array  $values
     * @return $this
     */
    public function whereNotIn($field, array $values)
    {
        $value = implode('.', $values);

        return $this->where('Ex.' . $field, $value, 'in');
    }

    /**
     * Filter field values with not certain value.
     *
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereNot($field, $value)
    {
        return $this->where('Ex.' . $field, $value);
    }

    /**
     * Filter for a specified pattern in a column.
     * can be used '%string', 'string%', or 'string'
     *
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereLike($field, $value)
    {
        $case = 'icontains';

        if (substr($value, 0, 1) === '%') {
            $case  = 'istartswith';
            $value = trim($value, '%');
        } else if (substr($value, -1) === '%') {
            $case  = 'iendswith';
            $value = trim($value, '%');
        }

        return $this->where($field, $value, $case);
    }

    /**
     * Filter value lower then (<).
     *
     * @param string $field
     * @param int    $value
     * @return $this
     */
    public function whereLt($field, $value)
    {
        return $this->where($field, $value, 'lt');
    }

    /**
     * Filter value lower then equal (<=).
     *
     * @param string $field
     * @param int    $value
     * @return $this
     */
    public function whereLte($field, $value)
    {
        return $this->where($field, $value, 'lte');
    }

    /**
     * Filter value greater then (>).
     *
     * @param string $field
     * @param int    $value
     * @return $this
     */
    public function whereGt($field, $value)
    {
        return $this->where($field, $value, 'gt');
    }

    /**
     * Filter value greater then equal (>=).
     *
     * @param string $field
     * @param int    $value
     * @return $this
     */
    public function whereGte($field, $value)
    {
        return $this->where($field, $value, 'gte');
    }

    /**
     * Filter values within a range.
     * values can be numbers, or dates
     *
     * @param string $field
     * @param mixed  $min
     * @param mixed  $max
     * @return $this
     */
    public function whereBetween($field, $min, $max)
    {
        return $this->where([$field . '.lte' => $max, $field . '.gte' => $min]);
    }

    /**
     * Filter field has NULL values
     *
     * @param string $field
     * @return $this
     */
    public function whereNull($field)
    {
        return $this->where($field, 1, 'isnull');
    }

    /**
     * Filter field not have NULL values
     *
     * @param string $field
     * @return $this
     */
    public function whereNotNull($field)
    {
        return $this->where($field, 1, 'notnull');
    }

    /**
     * Filter records based on more than one condition.
     *
     * @param string       $field
     * @param string|array $value
     * @param string       $expresion
     * @return $this
     */
    public function whereOr($field, $value, $expresion = null)
    {
        return $this->where('Or.' . $field, $value, $expresion);
    }

    /**
     * Chain to set conditional query.
     *
     * The expresion should one of the following:
     * exact/iexact, contains/icontains, gt/gte, lt/lte,
     * startswith/istartswith, endswith/iendswith,
     * in, between, notnull, isnull
     *
     * This condition by default will grouped with And.
     * Can be changed with prefix operator conditional. Prefix available:
     * And(default), Ex(AndNot), Or, OrNot
     *
     * @example  $repository->where(['id.lte' => 1, 'usergroup' => 1]);
     *           $repository->where('Or.id', 1, 'lte');
     *
     * @param string|array $condition can be an array or string
     * @param null         $value
     * @param null         $expresion
     * @return $this
     */
    public function where($condition, $value = null, $expresion = null)
    {
        if ($value) {
            $expresion = ($expresion) ? '.' . $expresion : null;
            $condition = [$condition . $expresion => $value];
        }

        $condition = is_array($condition) ? $condition : func_get_args();

        $this->conditions = array_merge((array) $this->conditions, array($condition));

        return $this;
    }

    /**
     * Compiling setter into bindings.
     *
     * @return array
     */
    public function compileBinding()
    {
        if ($properties = call_user_func('get_object_vars', $this)) {
            foreach ($properties as $key => $value) {
                if ($key == 'bindings') {
                    continue;
                }

                if (empty($value)) {
                    $this->setBinding($key, $value);
                    continue;
                };

                if ($key == 'conditions') {
                    $encode = json_encode($value, JSON_UNESCAPED_SLASHES);
                    $encode = str_replace([',"'], '%2C', $encode);
                    $encode = str_replace(['},'], '|', $encode);
                    $encode = str_replace(['{', '"', '}', '[', ']'], '', $encode);

                    $this->setBinding('conditions', $encode);
                } else {
                    if (is_array($value)) {
                        $this->setBinding($key, implode(',', $value));
                    } else {
                        $this->setBinding($key, $value);
                    }
                }

                $this->$key = null;
            }
        }

        return $this->getBinding();
    }

    /**
     * Get bindings.
     *
     * @param null $key
     * @return array|string
     */
    public function getBinding($key = null)
    {
        if ($this->bindings == null) {
            return false;
        }

        if ($key && key_exists($key, $this->bindings)) {
            return array_get($this->bindings, $key);
        }

        return array_filter($this->bindings,
            function ($value) {
                return ($value || is_numeric($value));
            }
        );
    }

    /**
     * Set binding values.
     *
     * @param string $key
     * @param string $values
     * @return $this
     */
    public function setBinding($key, $values)
    {
        if ($this->bindings != null && key_exists($key, $this->bindings)) {
            $this->bindings[$key] = $values;
        }

        return $this;
    }

    /**
     * Get Binding into query string.
     *
     * @param null $prefix
     * @return string
     */
    public function toString($prefix = null)
    {
        if ($binding = $this->getBinding()) {
            return $prefix . http_build_query($binding, '', '&', PHP_QUERY_RFC3986);
        }

        return false;
    }

    /**
     * Formating result for ngTable.
     *
     * @param      $result
     * @return array|null
     */
    protected function formatedResult($result)
    {
        $data = array(
            'data'   => [],
            'totals' => 0,
        );

        if ($result instanceof Collection && !$result->isEmpty()) {
            $data = array(
                'data'   => $result->all(),
                'totals' => $result->getTotal(),
            );
        }

        return $data;
    }

    /**
     * Make a json response for the result.
     *
     * @param $result
     * @return static
     */
    public function jsonResponse($result)
    {
        return JsonResponse::create($this->formatedResult($result), 200);
    }
}
