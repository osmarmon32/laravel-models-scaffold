<?php
//DO NOT MODIFY THIS CODE DIRECTLY MAKE YOUR MODELS INHERIT THIS CLASS AND MAKE YOUR CUSTOMIZATIONS THERE
namespace {{namespace}}\Models\Scaffold;
use {{scaffoldModelParent}} as ReddireccionModel;

class {{modelName}} extends ReddireccionModel {
    //{{softDelete}}
    protected $hidden = [
        //{{hidden}}
    ]; //won't be serialized
    protected $guarded = [
        //{{guarded}}
    ]; //won't be mass assigned
    protected $dates = [
        //{{dates}}
    ];
    protected static $_fieldsDefinition = [
        //{{fields}}
    ];
    //{{relationships}}
}