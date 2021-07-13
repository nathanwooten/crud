<?php

namespace nathanwooten\Crud;

use Exception;

use PDO;

/*
use nathanwooten\Application\{

	ControllerAbstract,

	Error\ErrorContext,
	Error\ErrorThrow

};
*/

class ControllerAbstract {

	public function __invoke() {

		$this->run( ...func_get_args() );

	}

}

interface ErrorContext
{

	public function handle( Exception $e );


}

class ErrorThrow
{

	public function handle( Exception $e )
	{

		throw $e;

	}

}

class ErrorPass
{

	public function hande( Exception $e )
	{

		return $e;

	}

}

abstract class Crud extends ControllerAbstract {

	protected PDO $pdo;

	protected $table;
	protected $properties = [];
	protected $parameters = [];

	protected $template = '';
	protected $vars = [];

	protected $type;

	public function __construct( PDO $pdo, $table, $properties = [], $parameters = [] )
	{

		$this->pdo = $pdo;
		$this->table = (string) $table;

		$this->setProperties( $properties );
		$this->setParameters( $this->createParameters( $parameters ) );

	}

	abstract public function run();

	protected function prepare( $sql, array $templateVars = [], array $parameters = [] ) {

		preg_match_all( '/\{\{.*?\}\}/', $sql, $match );
		if ( count( $match ) !== count( $templateVars ) ) {
			return $this->handleError( new Exception( 'Vars counts don\'t match' ) );
		}

		foreach ( $templateVars as $name => $value ) {

			$tag = '{{' . $name . '}}';
			$sql = str_replace( $tag, $value, $sql );
		}

		$stmt = $this->pdo->prepare( $sql );

		$i = 1;
		foreach ( $parameters as $values ) {
			foreach ( $values as $value ) {

				try {
					$stmt->bindParam( $i++, $value );
				} catch ( PDOException $e ) {
					print $e->getMessage();
					die();
				}
			}
		}

		return $stmt;

	}

	protected function execute( PDOStatement $stmt )
	{

		try {
			$result = $stmt->execute();
		} catch( PDOException $e ) {
			print $e->getMessage();
			die();
		}

		return $result;

	}

	protected function doExecute()
	{

		$stmt = $this->prepare( $this->template, $this->createVars(), $this->getParameters() );
		$result = $this->execute( $stmt );

		return $result;

	}

	protected function setProperties( array $properties ) {

		$output = [];

		foreach ( $properties as $name => $value ) {
			$this->createProperty( $name, $value );
		}

	}

	protected function setParameters( array $parameters )
	{

		$this->parameters = $parameters;

	}

	protected function createProperty( $name, $value )
	{

		$this->$name = $value;

	}

	public function createParameters( array $params ) {

		$parameters = [];
		$values = array_values( $this->parameters );

		foreach ( $this->parameters as $name => $v ) {
			$parameters[ $name ] = $params[ $name ];
		}

		return $parameters;

	}

	public function getParameter( $name ) {

		return isset( $this->parameters[ $name ] ) ? $this->parameters[ $name ] : null;

	}

	public function getParameters()
	{

		return $this->parameters;

	}

	public function createVars()
	{

		$vars = [];

		foreach ( $this->vars as $name => $v ) {
			$methodName = __FUNCTION__ . ucfirst( $name );

			$vars[ $name ] = $this->$methodName();
		}

		return $vars;

	}

	public function createVarsTable()
	{

		return $this->table;

	}

	public function createVarsColumns() {

		$for = $this->getColumns();
		$for = $for ? $for : $this->getValues();
var_dump( $for );
		if ( ! is_array( $for ) ) {
			return $this->handleError( new Exception( 'Can not create columns for they have not been provided' ) );
		}

		$values = [];
		foreach ( $this->getValues() as $key => $value ) {

			$values[] = $key . '=' . '?';
		}

		$columns = implode( ', ', $values );

		return $columns;

	}

	public function createVarsValues() {

		$values = implode( ', ', array_fill( 1, count( $this->getValues() ), '?' ) );
		return $values;

	}

	public function createVarsWhere()
	{

		$where = $this->getWhere();

		foreach( $where as $column => $value ) {
			$var[] = $column . '=?';
		}
		$var = implode( ' && ', $var );

		return $var;

	}

	public function __call( $method, $args = null ) {

		$parameter = ltrim( strtolower( $method ), 'get' );
		return $this->getParameter( $parameter );

	}

	public function handleError( Exception $e )
	{

		return $this->errorContext()->handle( $e );

	}

	protected function errorContext()
	{

		if ( ! isset( $this->errorContext ) ) {
			$this->errorContext = new ErrorThrow;
		}

		return $this->errorContext;

	}

}
