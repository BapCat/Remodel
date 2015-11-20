<?php namespace BapCat\Remodel;

use ICanBoogie\Inflector;

function titlize($input) {
  return Inflector::get()->camelize($input);
}

function camelize($input) {
  return Inflector::get()->camelize($input, true);
}

function underscore($input) {
  return Inflector::get()->underscore($input);
}

function pluralize($input) {
  return Inflector::get()->pluralize($input);
}

function singularize($input) {
  return Inflector::get()->singularize($input);
}

function humanize($input) {
  return Inflector::get()->humanize($input);
}
