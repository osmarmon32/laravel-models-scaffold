<?php

namespace Reddireccion\ModelsScaffold\Database;

use \Illuminate\Database\MySqlConnection AS IlluminateMySqlConnection;
use \Reddireccion\ModelsScaffold\Database\MySqlGrammar;
use \Reddireccion\ModelsScaffold\Database\MySqlBuilder;

class MySqlConnection extends IlluminateMySqlConnection{
    /*
     * Get a schema builder instance for the connection
     * @return \App\Extensions\ExtendedMySqlBuilder
     * */
    public function getSchemaBuilder(){
        if (is_null($this->schemaGrammar)) { $this->useDefaultSchemaGrammar(); }

        return new MySqlBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Schema\Grammars\MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new MySqlGrammar);
    }
}
