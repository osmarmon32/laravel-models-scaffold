<?php

namespace Reddireccion\ModelsScaffold\Console;

use Illuminate\Console\Command;
use \Config, \DB, \File;
/**
 * Creates model(s) scaffold based on the mysql table structure
 *
 * This command modify the entry points like index files and artisan command to use a constant called MULTI_APP_NAME
 * app.php file or /app path are copied to files and paths with the name of the new app 
 * and MULTI_APP_NAME is then replaced in all files that references to those files or paths
 * to allow multiple applications running and sharing under the same laravel framework code 
 *
 */
class CreateModelScaffold extends Command
{
    /**
     * The name of the table for wich we are creating a model
     *
     * @var string
     */
    public $tableName="";
    /**
     * comma separated list of tables to not generate model for. by default, migrations table does not need a model.
     *
     * @var string
     */
    public $skipTables="";
    /**
     * Prefix for generating only the tables with this prefix, by default use the app prefix specially if you share the database with multiple applications by using prefix.
     *
     * @var string
     */
    public $startsWith='';
    /**
     * Comma separated list of fields that should be guarded by default in the generated models, by default the timestamps, modifiers and id are guarded
     *
     * @var string
     */
    public $guardedFields='';
    /**
     * The path from the root folder where the template for the scaffold model is located, by default the one from the package folder
     *
     * @var string
     */
    public $scaffoldTemplatePath='';
    /**
     * The path from the root folder where the template for the empty model is located, by default the one from the package folder
     *
     * @var string
     */
    public $emptyModelTemplatePath='';
    /**
     * The path where the models will be stored, by default the Models folder inside the app folder. 
     *
     * @var string
     */
    public $modelsPath='';
    /**
     * The namespace that the models will share, by default {name of the app}\Models
     *
     * @var string
     */
    public $modelsNamespace='';
    /**
     * The name (path) of the database connection by default database.connections.mysql
     *
     * @var string
     */
    public $databaseConnectionName='';
    /**
     * To define a custom name for the model, by default the table name singularized will be used
     *
     * @var string
     */
    protected $modelName='';

    /**
     * To define a custom name for the model, by default the table name singularized will be used
     *
     * @var string
     */
    protected $scaffoldModelBase='\\Reddireccion\\ModelsScaffold\\Models\\ReddireccionModel';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model-scaffold {tableName?} {skipTables?} {startsWith?} {guardedFields?} {scaffoldTemplatePath?} {emptyModelTemplatePath?} {modelsPath?} {modelsNamespace?} {databaseConnectionName?} {scaffoldModelBase?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new model scaffold based on table structure and comments';

    /**
     * array of strings: Default list of tables that won't generate a model 
     *
     * @var string
     */
    protected $skipTablesDefault = ['migrations'];
    /**
     * array of strings: Default list of fields that are guarded 
     *
     * @var string
     */
    protected $guardedFieldsDefault = ['id','created_by','updated_by','created_at','updated_at','deleted_at'];
    /**
     * default path of the scaffold model template
     *
     * @var string
     */
    protected $scaffoldTemplatePathDefault='packages/reddireccion/modelsscaffold/src/Template/ScaffoldModel.php';
    /**
     * default path of the empty model template
     *
     * @var string
     */
    protected $emptyModelTemplatePathDefault='packages/reddireccion/modelsscaffold/src/Template/EmptyModel.php';
    /**
     * default models path, will be generated in constructor
     *
     * @var string
     */
    protected $modelsPathDefault='';
    /**
     * default namespece of models, will be generated in constructor 
     *
     * @var string
     */
    protected $modelsNamespaceDefault='';
    /**
     * default name of the database connection, we'll use the default mysql path 
     *
     * @var string
     */
    protected $databaseConnectionNameDefault='database.connections.mysql';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->modelsPathDefault=base_path($this->getAppName()."/Models");
        $this->modelsNamespaceDefault="\\".$this->getAppName()."\\Models\\";
        File::makeDirectory($this->modelsPathDefault.'/Scaffold', 0777, true, true);
        parent::__construct();
    }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->schema = Config::get($this->getDatabaseConnectionName().'.database');
        $this->tableName = $this->argument('tableName');
        if($this->tableName){
            $this->createSingleModelFile($this->tableName);
        }else{
            $this->createAllModelFiles();
        }

        $this->line('Ready.');
    }

    /**
     * If multiapp name is defined use that one otherwise use the default
     * TODO: create a helper and use it
     *
     * @return string app name
     */
    public function getAppName(){
        if(defined('MULTI_APP_NAME'))
            return MULTI_APP_NAME;
        return 'app';
    }
    /**
     * Return the default app namespace from the app name
     *
     * @return string app namespace
     */
    public function getAppNamespace(){
        return studly_case($this->getAppName());
    }

    /**
     * set the table name, initialize a new scaffold template and sets model name 
     *
     * @return void
     */
    public function setup($tableName){
        $this->tableName=$tableName;
        $this->modelName='';
        $this->template = $this->getScaffoldTemplate();
        $this->modelName=$this->getModelName($tableName);
    }
    /**
     * get the database connection name from custom or default value
     *
     * @return string database connection name
     */
    public function getDatabaseConnectionName(){
        if($this->databaseConnectionName)
            return $this->databaseConnectionName;
        return $this->databaseConnectionNameDefault;
    }
    /**
     * get the models path from custom or default value
     *
     * @return string models path
     */
    public function getModelsPath(){
        if($this->modelsPath)
            return $this->modelsPath;
        return $this->modelsPathDefault;
    }
    /**
     * get the models namespace from custom or default value
     *
     * @return string model namespace
     */
    public function getModelsNamespace(){
        if($this->modelsNamespace)
            return $this->modelsNamespace;
        return $this->modelsNamespaceDefault;
    }
    /**
     * get the empty models template path from custom or default value
     *
     * @return string empty model template path
     */
    public function getEmptyModelTemplatePath(){
        if($this->emptyModelTemplatePath)
            return $this->emptyModelTemplatePath;
        return $this->emptyModelTemplatePathDefault;
    }
    /**
     * get the scaffold template path from custom or default value
     *
     * @return string scaffold template path
     */
    public function getScaffoldTemplatePath(){
        if($this->scaffoldTemplatePath)
            return $this->scaffoldTemplatePath;
        return $this->scaffoldTemplatePathDefault;
    }
    /**
     * get model name from custom or default value
     *
     * @return string model name
     */
    public function getModelName($tableName){
        if($this->modelName)
            return $this->modelName;
        return studly_case(str_singular(str_replace($this->getPrefix(),'', $tableName)));
    }
    /**
     * reads the scaffold template
     *
     * @return string pristine scaffold template
     */
    public function getScaffoldTemplate(){
        return File::get(base_path($this->getScaffoldTemplatePath()));
    }
    /**
     * get model name from custom or default value
     *
     * @return string app name
     */
    public function getEmptyModelTemplate(){
        return File::get(base_path($this->getEmptyModelTemplatePath()));
    }
    /**
     * get array of tables to skip from custom or default value
     *
     * @return array of string 
     */
    public function getSkipTables(){
        if($this->skipTables!=''){
            return explode(',', $this->skipTables);
        }
        return $this->skipTablesDefault;
    }
    /**
     * get guarded fields from custom or default value
     *
     * @return array of string
     */
    public function getGuardedFields(){
        if($this->guardedFields!=''){
            return explode(',', $this->guardedFields);
        }
        return $this->guardedFieldsDefault;
    }
    /**
     * get tables prefix from custom value or database config file
     *
     * @return string
     */
    public function getPrefix(){
        if($this->startsWith)
            return $this->startsWith;
        return Config::get($this->getDatabaseConnectionName().'.prefix');
    }

    /**
     * querys the information schema database to get the list of all the tables and iterates over the result to generate the model of each one unless it's on the skips table array.
     *
     * @return void
     */
    public function createAllModelFiles(){
        $orderBy="SUBSTRING(SUBSTRING(table_comment,LOCATE('step',table_comment)+6),1,IF(LOCATE(',',SUBSTRING(table_comment,LOCATE('step',table_comment)+6))='',LOCATE('}',SUBSTRING(table_comment,LOCATE('step',table_comment)+6)),LOCATE(',',SUBSTRING(table_comment,LOCATE('step',table_comment)+6)))-1)";
        //get all tables with comments to create a model for each one.        
        $results = DB::select("SELECT table_name, table_comment FROM information_schema.tables WHERE table_schema = '{$this->schema}' ORDER BY {$orderBy}, table_name ASC");
        $skipTables =$this->getSkipTables();
        $prefix = $this->getPrefix();
        //for each table in the schema
        foreach($results as $item){
            //Except migrations table
            if(!in_array($item->table_name, $skipTables)){
                //create model based on table info
                if($prefix && !starts_with($item->table_name,$prefix))
                    continue;
                $this->createModelFile($item->table_name,$item->table_comment);
            }
        }
    }

    /**
     * Query the information schema to get a single table definition and builds the model
     *
     * @param string table name
     * @return void
     */
    public function createSingleModelFile($tableName){
        //get all tables with comments to create a model for each one.
        $results = DB::select("SELECT table_name, table_comment FROM information_schema.tables WHERE table_schema = '{$this->schema}' AND table_name='{$tableName}' ORDER BY table_comment, table_name ASC")->pluck('table_name','table_comment');
        if(!is_null($result)){
            foreach($results as $tableName=>$tableComment){
                $this->createModelFile($tableName, $tableComment);
            }
        }
    }

    /**
     * Setup the generator Generator and generates the model files for a single table
     *
     * @param string: name of the table for which the model files will be generated
     * @param optional string: the comments of the table for special instructions
     * @return void
     */
    public function createModelFile($tableName,$comment=''){
        $this->setup($tableName);
        $this->line("Generating model: ".$this->modelName);
        $this->populateModelTemplate();
    }
    /**
     * Querys the information schema table to get the columns definition and populates the model templates
     *
     * @return void
     */
    public function populateModelTemplate(){
        //Set ModelName and namespace
        $this->template = str_replace('{{modelName}}', $this->modelName,$this->template);
        $this->template = str_replace('{{namespace}}', $this->getAppNamespace(),$this->template);
        $this->template = str_replace('{{scaffoldModelParent}}', $this->scaffoldModelBase, $this->template);
        
        $tableFields = $this->executeQuery("SELECT column_name, data_type, is_nullable, column_type, column_comment, IFNULL(character_maximum_length,IFNULL(DATETIME_PRECISION, NUMERIC_PRECISION+IFNULL(NUMERIC_SCALE,0))) AS length FROM information_schema.`COLUMNS` WHERE table_schema = '{$this->schema}' AND table_name='{$this->tableName}'");
        foreach($tableFields as $field){
            $field['column_comment']=json_decode($field['column_comment'],true);
            $this->processField($field);
        }
        $this->cleanVariables();
        $this->saveModelScaffold();
        $this->saveEmptyModel();
    }
    /**
     * helper that executes a query in fetch assoc mode
     *
     * @param string query to execute
     * @return array rows of result for the query
     */
    protected function executeQuery($query){
        $pdo = DB::getPdo();
        $statement = $pdo->prepare($query);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->execute();
        $results = $statement->fetchAll();
        return $results;
    }
    /**
     * removes variable names from template
     *
     * @return void
     */
    public function cleanVariables(){
        $this->template=str_replace('//{{hidden}}', '', $this->template);
        $this->template=str_replace('//{{guarded}}', '', $this->template);
        $this->template=str_replace('//{{softDelete}}', '', $this->template);
        $this->template=str_replace('//{{fields}}', '', $this->template);
        $this->template=str_replace('//{{dates}}', '', $this->template);
    }
    /**
     * process a single column to generate the field definition with relationships and fill othe model properties like guarded, hidden, etc
     *
     * @return void
     */
    public function processField($field){
        $this->fillFieldDefinition($field);
        $this->fillRelationship($field);
        $this->fillGuarded($field);
        $this->fillDates($field);
        $this->fillHidden($field);
        $this->fillSoftDeletes($field);
    }
    /**
     * generates a row in the fields definition array defined in the package
     *
     * @return void
     */
    public function fillFieldDefinition($field){
        extract($field); 
        $is_nullable=$is_nullable=='YES'?true:false;
        $data=[
            'type'=>$data_type,
            'length'=>$length,
            'nullable'=>$is_nullable,
            'comments'=>$column_comment
        ];
        $definition="'{$column_name}'=>".var_export($data,true);
        $this->template = str_replace('{{fields}}','{{fields}}'."\r\n\t\t".$definition.',' ,$this->template);
    }
    /**
     * if valid, add a new field to the guarded property of the model
     *
     * @return void
     */
    public function fillGuarded($field){
        if(in_array($field['column_name'], $this->getGuardedFields())){
            $this->template = str_replace('{{guarded}}','{{guarded}}'."\r\n\t\t'".$field['column_name'].'\',' ,$this->template);
        }
    }
    /**
     * if the type of the field is timestamp add the field to the dates property of the model
     *
     * @return void
     */
    public function fillDates($field){
        if($field['data_type']=='timestamp'){
            $this->template = str_replace('{{dates}}','{{dates}}'."\r\n\t\t'".$field['column_name'].'\',' ,$this->template);
        }
    }
    /**
     * if the custom comment instruction is found, adds the field to the hidden property of the model
     *
     * @return void
     */
    public function fillHidden($field){
        if($this->getCommentProperty($field,'hidden')==true){
            $this->template = str_replace('{{hidden}}','{{hidden}}'."\r\n\t\t'".$field['column_name'].'\',' ,$this->template);
        }
    }
    /**
     * if the field is "deleted_at" adds the softDeletes trait to the model
     *
     * @return void
     */
    public function fillSoftDeletes($field){
        if($field['column_name']=='deleted_at'){
            $this->template = str_replace('//{{softDelete}}', 'use SoftDeletes;', $this->template);
        }
    }
    /**
     * if the field is a foreignt key adds a relationship in both the current model and the related one
     *
     * @return void
     */
    public function fillRelationship($field){
        if(ends_with($field['column_name'], '_id') && $field['data_type']=='int'){
            $modelName = $this->getCommentProperty($field,'modelName');
            if(!$modelName){
                $modelName=studly_case(substr($field['column_name'],0,-3));
            }
            $functionName = $this->getCommentProperty($field,'functionName');
            if(!$functionName){
                $functionName = camel_case($modelName);
            }
            $relationship = "public function {$functionName}(){
                \r\n\t\t\$this->belongsTo('{$this->getModelsNamespace()}{$modelName}','{$field['column_name']}');
            \r\n\t}";
            $this->template=str_replace('{{relationships}}', '{{relationships}}'."\r\n\t".$relationship, $this->template);
            $this->fillRelatedModel($field,$modelName);
        }
    }
    /**
     * add a relationship in the related model file
     *
     * @return void
     */
    public function fillRelatedModel($field,$modelName){
        $relatedModelPath =$this->getModelsPath().'/Scaffold/'.$modelName.'.php'; 
        if(File::exists($relatedModelPath)){
            $txt = File::get($relatedModelPath);

            $functionName = $this->getCommentProperty($field,'relatedModelFunctionName');
            if(!$functionName){
                $functionName = camel_case(str_plural($this->modelName));
            }
            $relationshipType = $this->getCommentProperty($field,'hasOne')!=true? 'hasMany':'hasOne';
            $relationship = "public function {$functionName}()\{
                \r\n\t\t\t\$this->{$relationshipType}('{$this->getModelsNamespace()}{$modelName}','id',{$field['column_name']});
            \r\n\t\t\}";

            File::put($relatedModelPath, $txt);
        }
    }
    /**
     * saves the generated scaffold template in a new file for the model
     *
     * @return array rows of result for the query
     */
    public function saveModelScaffold(){
        $filename=$this->getModelsPath().'/Scaffold/'.$this->modelName.'.php';
        File::put($filename,$this->template);
        $this->template='';
    }
    /**
     * If no app model exists with that name, an empty one is created extending the scaffold
     *
     * @return void
     */
    public function saveEmptyModel(){
        if(!File::exists($this->getModelsPath().'/'.$this->modelName.'.php')){
            $txt = $this->getEmptyModelTemplate();
            $txt=str_replace('{{modelName}}', $this->modelName, $txt);
            $txt = str_replace('{{namespace}}', $this->getAppNamespace(),$txt);
            File::put($this->getModelsPath().'/'.$this->modelName.'.php',$txt);
        }
    }
    /**
     * get a property int the comments from columns definition 
     *
     * @return void
     */
    protected function getCommentProperty($field,$propertyName){
        if(!is_null($field['column_comment']) && array_key_exists($propertyName, $field['column_comment']))
            return $field['column_comment'][$propertyName];
        return null;
    }
}
