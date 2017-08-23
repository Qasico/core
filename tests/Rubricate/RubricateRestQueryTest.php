<?php

use Qasico\Rubricate\RestQuery;

class RubricateRestQueryTest extends TestCase
{
    public function testQuerySetterSelect()
    {
        $q = $this->getQuery();

        $q->select('id');
        $this->assertEquals(['id'], $q->fields);

        $q->select(['name', 'email']);
        $this->assertEquals(['name', 'email'], $q->fields);
    }

    public function testQuerySetterJoin()
    {
        $q = $this->getQuery();

        $q->join(false);
        $this->assertEquals('none', $q->join);

        $q->join('name');
        $this->assertEquals(['name'], $q->join);

        $q->join(['name', 'email']);
        $this->assertEquals(['name', 'email'], $q->join);
    }

    public function testQuerySetterGroup()
    {
        $q = $this->getQuery();

        $q->groupBy('name');
        $this->assertEquals(['name'], $q->groupby);

        $q->groupBy(['name', 'email']);
        $this->assertEquals(['name', 'email'], $q->groupby);
    }

    public function testQuerySetterOrder()
    {
        $q = $this->getQuery();
        $q->order(['id' => 'asc', 'name' => 'desc']);

        $this->assertEquals(['id', 'name'], $q->sortby);
        $this->assertEquals(['asc', 'desc'], $q->order);
    }
    
    public function testQuerySetterOrderBinding()
    {
        $q = $this->getQuery();
        
        $q->order(['id' => 'desc']);
        $q->order(['total' => 'asc', 'name' => 'desc']);
    
        $this->assertEquals(['total','name', 'id'], $q->sortby);
        $this->assertEquals(['asc', 'desc', 'desc'], $q->order);
    }

    public function testQuerySetterLimit()
    {
        $q = $this->getQuery();

        $q->limit(10);
        $this->assertEquals(10, $q->limit);
    }

    public function testQuerySetterOffset()
    {
        $q = $this->getQuery();

        $q->offset(4);
        $this->assertEquals(4, $q->offset);
    }

    public function testQuerySetterWhere()
    {
        $q = $this->getQuery();

        $q->where('id', 1);
        $this->assertEquals([['id' => 1]], $q->query, 'wew');

        $q->where('id', 1, 'lte');
        $this->assertEquals([['id' => 1], ['id.lte' => 1]], $q->query);

        $q->where(['name' => 'alif', 'email' => 'alifamri@qasico.com']);
        $this->assertEquals([['id' => 1], ['id.lte' => 1], ['name' => 'alif', 'email' => 'alifamri@qasico.com']], $q->query);
    }

    public function testQuerySetterWhereIn()
    {
        $q = $this->getQuery();

        $q->whereIn('id', [1, 2]);
        $this->assertEquals([['id.in' => '1.2']], $q->query);
    }

    public function testQuerySetterWhereNotIn()
    {
        $q = $this->getQuery();

        $q->whereNotIn('id', array(1, 3));
        $this->assertEquals([['Ex.id.in' => '1.3']], $q->query);
    }

    public function testQuerySetterWhereNot()
    {
        $q = $this->getQuery();

        $q->whereNot('id', 1);
        $this->assertEquals([['Ex.id' => 1]], $q->query);
    }

    public function testQuerySetterWhereOr()
    {
        $q = $this->getQuery();

        $q->whereOr('id', 1);
        $this->assertEquals([['Or.id' => 1]], $q->query);
    }

    public function testQuerySetterWhereLike()
    {
        $q = $this->getQuery();

        $q->whereLike('name', 'alif');
        $this->assertEquals([['name.icontains' => 'alif']], $q->query);

        $q = $this->getQuery();

        $q->whereLike('name', '%alif');
        $this->assertEquals([['name.istartswith' => 'alif']], $q->query);

        $q = $this->getQuery();

        $q->whereLike('name', 'alif%');
        $this->assertEquals([['name.iendswith' => 'alif']], $q->query);
    }

    public function testQuerySetterWhereBetween()
    {
        $q = $this->getQuery();

        $q->whereBetween('amount', 10, 100);
        $this->assertEquals([['amount.gte' => 10, 'amount.lte' => 100]], $q->query);
    }

    public function testQuerySetterWhereNull()
    {
        $q = $this->getQuery();

        $q->whereNull('amount');
        $this->assertEquals([['amount.isnull' => 1]], $q->query);
    }

    public function testQuerySetterWhereNotNull()
    {
        $q = $this->getQuery();

        $q->whereNotNull('amount');
        $this->assertEquals([['amount.notnull' => 1]], $q->query);
    }

    public function testQueryCombineBinding()
    {
        $q = $this->getQuery();

        $q->select(['name', 'email']);
        $q->join(['name', 'email']);
        $q->groupBy(['name', 'email']);
        $q->order(['id' => 'asc', 'name' => 'desc']);
        $q->limit(10);
        $q->offset(4);
        $q->where(['name' => 'alif, amri and suri', 'email' => 'alifamri@qasico.com']);
        $q->whereIn('id', [1, 2]);
        $q->whereNotIn('id', array(1, 3));
        $q->whereNot('id', 1);
        $q->whereOr('id', 1);
        $q->whereLike('name', 'alif');
        $q->whereBetween('amount', 10, 100);
        $q->whereNull('amount');
        $q->whereNotNull('amount');

        $q->compileBinding();

        $this->assertEquals('name,email', $q->getBinding('fields'));
        $this->assertEquals('name,email', $q->getBinding('groupby'));
        $this->assertEquals('name,email', $q->getBinding('join'));
        $this->assertEquals(10, $q->getBinding('limit'));
        $this->assertEquals(4, $q->getBinding('offset'));
        $this->assertEquals('id,name', $q->getBinding('sortby'));
        $this->assertEquals('asc,desc', $q->getBinding('order'));
        $this->assertEquals('name:alif, amri and suri%2Cemail:alifamri@qasico.com|id.in:1.2|Ex.id.in:1.3|Ex.id:1|Or.id:1|name.icontains:alif|amount.lte:100%2Camount.gte:10|amount.isnull:1|amount.notnull:1', $q->getBinding('query'));
    }

    public function testQueryToString()
    {
        $q = $this->getQuery();

        $this->assertFalse($q->toString());

        $q->select(['name', 'email']);
        $q->compileBinding();

        $this->assertEquals('fields=name%2Cemail', $q->toString());
    }

    protected function getQuery()
    {
        return new RestQuery();
    }
}