<?php

namespace Reddireccion\ModelsScaffold\Database;

use \Illuminate\Database\Schema\Blueprint AS IlluminateBlueprint;
/**
 * 
 */
class Blueprint extends IlluminateBlueprint {
	
	/**
	 * Add creation and update timestamps to the table.
	 *
	 * @return void
	 */
	public function doers()
	{
		$this->integer('created_by');
		$this->integer('updated_by');
	}
	
	/**
	 * Add primary key and audit fields
	 *
	 * @return void
	 */
	public function basics()
	{
	    $this->increments('id');
	    $this->timestamps();
		$this->softDeletes();
        $this->doers();
	}
}
