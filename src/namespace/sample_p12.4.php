<?php namespace sample;
/**
 * Created by JetBrains PhpStorm.
 * User: gmort
 * Date: 11/9/12
 * Time: 5:16 PM
 * To change this template use File | Settings | File Templates.
 */


require __DIR__.'/../../build/joomla.ns.phar';


// create JObject using a namespace alias
use \joomla\joomla\p12_4 as j;
$jobject1 = new j\JObject();

// should see the class namespace here
var_dump($jobject1);

// create JObject from within the namespace
namespace joomla\joomla\p12_4;
$j2 = new JObject();
var_dump($j2);