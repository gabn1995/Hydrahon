<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Translator
 * @group Hydrahon_Translator_Mysql
 */

use ClanCats\Hydrahon\Query\Sql\BaseSql;
use ClanCats\Hydrahon\Query\Expression;

class Translator_Mysql_Test extends TranslatorCase
{
	protected $grammar = 'mysql';

	/**
	 * mysql grammar tests
	 */
	public function testSelectSimple()
	{
		$this->assertQueryTranslation('select * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select();
		});

		// distinct
		$this->assertQueryTranslation('select distinct * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->distinct();
		});

		// with database 
		$this->assertQueryTranslation('select distinct * from `db_phpunit`.`users` as `u`', array(), function($q) 
		{
			return $q->table('db_phpunit.users as u')->select()->distinct();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectFields()
	{
		$this->assertQueryTranslation('select `id` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('id');
		});

		// comma seperated fields
		$this->assertQueryTranslation('select `foo`, `bar` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('foo, bar');
		});

		// with alias as string
		$this->assertQueryTranslation('select `foo`, `bar` as `b` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('foo, bar as b');
		});

		// with array
		$this->assertQueryTranslation('select `name`, `age` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array('name', 'age'));
		});

		// with array with alias
		$this->assertQueryTranslation('select `name` as `n`, `age` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array('name' => 'n', 'age'));
		});

		// with raw 
		$this->assertQueryTranslation('select count(id) as count from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array(new Expression('count(id) as count')));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testWhere()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ?', array(42), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', 42);
		});

		// diffrent expression
		$this->assertQueryTranslation('select * from `phpunit` where `id` != ?', array(42), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', '!=', 42);
		});

		// raw value
		$this->assertQueryTranslation('select * from `phpunit` where `id` != 42', array(), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', '!=', new Expression('42'));
		});

		// 2 wheres
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ? and `active` = ?', array(42, 1), function($q) 
		{
			return $q->table('phpunit')->select()
				->where('id', 42 )
				->where('active', 1);
		});

		// 2 wheres or
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ? or `active` = ?', array(42, 1), function($q) 
		{
			return $q->table('phpunit')->select()
				->where('id', 42 )
				->orWhere('active', 1);
		});

		// nesting
		$this->assertQueryTranslation('select * from `phpunit` where ( `a` = ? or `c` = ? )', array('b', 'd'), function($q) 
		{
			return $q->table('phpunit')->select()
				->where(function( $q )
				{
					$q->where('a', 'b');
					$q->orWhere('c', 'd');
				});
		});

		// arrays
		$this->assertQueryTranslation('select * from `phpunit` where ( `name` = ? and `age` = ? )', array('foo', 18), function($q) 
		{
			return $q->table('phpunit')->select()
				->where(array( 'name' => 'foo', 'age' => 18 ));
		});

		//  where in
		$this->assertQueryTranslation('select * from `phpunit` where `id` in (?, ?, ?)', array(23, 213, 53), function($q) 
		{
			return $q->table('phpunit')->select()
				->whereIn('id', array(23, 213, 53));
		});

		//  where null
		$this->assertQueryTranslation('select * from `phpunit` where `user`.`updated` is NULL', array(), function($q) 
		{
			return $q->table('phpunit')->select()
				->whereNull('user.updated');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testLimit()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` limit 0, 1', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit(1);
		});

		// with offset
		$this->assertQueryTranslation('select * from `phpunit` limit 10, 20', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit(10, 20);
		});

		// invalid stuff
		$this->assertQueryTranslation('select * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit('ignore');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectOrderBy()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` order by `id` asc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('id');
		});

		// other direction
		$this->assertQueryTranslation('select * from `phpunit` order by `id` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('id', 'desc');
		});

		// more keys comma seperated
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` desc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('u.firstname, u.lastname', 'desc');
		});

		// column array
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` desc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy(array('u.firstname', 'u.lastname'), 'desc');
		});

		// multipe sortings diffrent direction
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` asc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy(array('u.firstname' => 'asc', 'u.lastname' => 'desc'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectGroupBy()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` group by `user`.`group`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy('user.group');
		});

		// comma seperated
		$this->assertQueryTranslation('select * from `phpunit` group by `user`.`group`, `group`.`key`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy('user.group, group.key');
		});

		// array
		$this->assertQueryTranslation('select * from `phpunit` group by `foo`, `bar`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy(array('foo', 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectJoins()
	{
		// simple
		$this->assertQueryTranslation('select * from `db1`.`users` as `u` left join `db1`.`groups` as `g` on `u`.`id` = `g`.`user_id`', array(), function($q) 
		{
			return $q->table('db1.users as u')->select()->join('db1.groups as g', 'u.id', '=', 'g.user_id');
		});

		// other types
		$this->assertQueryTranslation('select * from `db1`.`users` as `u` left join `db1`.`groups` as `g` on `u`.`id` = `g`.`user_id` right join `db1`.`orders` as `o` on `u`.`id` = `o`.`user_id` inner join `profiles` as `p` on `u`.`id` = `p`.`user_id`', array(), function($q) 
		{
			return $q->table('db1.users as u')->select()
				->join('db1.groups as g', 'u.id', '=', 'g.user_id')
				->rightJoin('db1.orders as o', 'u.id', '=', 'o.user_id')
				->innerJoin('profiles as p', 'u.id', '=', 'p.user_id');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertSimple()
	{
		// simple
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar'));
		});

		// some more complexity
		$this->assertQueryTranslation('insert into `test` (`bar`, `foo`) values (?, ?)', array('foo','bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertIgnore()
	{
		// ignore
		$this->assertQueryTranslation('insert ignore into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test')->insert()->ignore()->values(array('foo' => 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertBulk()
	{
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?), (?)', array('bar', 'bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array(array('foo' => 'bar'), array('foo' => 'bar')));
		});

		// 2x add valies
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?), (?)', array('bar', 'bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar'))->values(array('foo' => 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testUpdateSimple()
	{
		// simple
		$this->assertQueryTranslation('update `test` set `foo` = ?', array('bar'), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar'));
		});

		$this->assertQueryTranslation('update `test` set `foo` = ?, `bar` = ?', array('bar', 'foo'), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testUpdateWithWhereAndLimit()
	{
		// simple
		$this->assertQueryTranslation('update `test` set `foo` = ? where `id` = ? limit 0, 1', array('bar', 1), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar'))->where('id', 1)->limit(1);
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testDelete()
	{
		// simple
		$this->assertQueryTranslation('delete from `test` where `id` = ? limit 0, 1', array(1), function($q) 
		{
			return $q->table('test')->delete()->where('id', 1)->limit(1);
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testDrop()
	{
		// simple
		$this->assertQueryTranslation('drop table `test`;', array(), function($q) 
		{
			return $q->table('test')->drop();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testTruncate()
	{
		// simple
		$this->assertQueryTranslation('truncate table `test`;', array(), function($q) 
		{
			return $q->table('test')->truncate();
		});
	}
}