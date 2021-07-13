<?php

namespace nathanwooten\Crud;

class CrudUpdate extends CrudStatement
{

	protected $template = 'update {{table}} set {{columns}} where {{where}};';

	protected $table;

	protected $vars = [
		'table' => '',
		'columns' => '',
		'values' => '',
		'where' => ''
	];

	protected $parameters = [
		'values' => [],
		'where' => []
	];

	public function __construct( PDO $pdo, $table, array $parameters ) {

		parent::__construct( $pdo, $table, $parameters );

	}

	public function run() {

		$result = $this->doExecute( $this->getTable(), $this->getValues(), $this->getWhere() );
		return $result;

	}

}
