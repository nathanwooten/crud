<?php

namespace nathanwooten\Crud\Statement;

use PDO;

use nathanwooten\Crud\Crud;

class CrudSelect extends Crud
{

	protected $type = 'select';
	protected $template = 'select {{columns}} FROM {{table}} {{where}};';

	protected $table;

	protected $properties = [
		'columns' => []
	];

	protected $vars = [
		'table' => '',
		'columns' => '',
		'where' => ''
	];

	protected $parameters = [
		'where' => []
	];

	public function __construct( PDO $pdo, $table, array $properties, array $parameters ) {

		parent::__construct( $pdo, $table, $properties, $parameters );

	}

	protected function setTable( $table )
	{

		$this->table = (string) $table;

	}

	public function run() {

		$result = $this->doExecute( $this->getTable(), $this->getColumns(), $this->getWhere() );
		return $result;

	}

}
