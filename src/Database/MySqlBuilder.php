<?php

namespace Reddireccion\ModelsScaffold\Database;

use \Illuminate\Database\Schema\MySqlBuilder AS IlluminateMySqlBuilder;
use \Reddireccion\ModelsScaffold\Database\Blueprint;

class MySqlBuilder extends IlluminateMySqlBuilder{
	/*Create a new command with a closure
	 * @param 	string	$table
	 * @param	Closure	$callback
	 * @return  Extensions\ExtendedMysqlBuilder
	 * */
	 protected function createBlueprint($table, \Closure $callback=null)
	 {
		if (isset($this->resolver))
		{
			return call_user_func($this->resolver, $table, $callback);
		}
		else
		{
			return new Blueprint($table, $callback);
		}
	 }
}
