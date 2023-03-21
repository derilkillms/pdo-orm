<?php
/**
 * Author   : Muhammad Deril
 * URI      : http://www.derillab.com
 * Github   : @derilkillms
 */

namespace Derilkillms\PdoOrm;
use PDO;

class Database{

	private $dbh;
	private $stmt;

	public function __construct(){

		GLOBAL $CFG;
        //database source name
		$dsn = $CFG->dbtype.':host='.$CFG->dbhost.';dbname='.$CFG->dbname;

		$option = [
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		];

		try{
			$this->dbh = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, $option);
		} catch(PDOException $e){
			die($e->getMessage());
		}
	}


	protected function fix_table_names($sql) {
		return preg_replace_callback(
			'/\{([a-z][a-z0-9_]*)\}/',
			function($matches) {
				return $this->fix_table_name($matches[1]);
			},
			$sql
		);
	}

	protected function fix_table_name($tablename) {
		GLOBAL $CFG;
		return $CFG->prefix . $tablename;
	}

	public function query($query){
		$this->stmt = $this->dbh->prepare($this->fix_table_names($query));
	}


	public function execute_sql($params){

		$this->stmt->execute($params);
	}

	public function execute($sql=null, $params = array()){
		$this->query($sql);
		
		$this->execute_sql($params);
		return $this->rowCount();
	}

	public function resultSet($params){
		$this->execute_sql($params);
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);

	}
	public function singgle($params){
		$this->execute_sql($params);
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}


	public function rowCount(){
		return $this->stmt->rowCount();
	}

	public function get_records_sql($query='',$params=array())
	{
		$this->query($query);


		return json_decode(json_encode($this->resultSet($params)));
	}

	public function get_record_sql($query='',$params=array())
	{
		$this->query($query);


		return (object) $this->singgle($params);


	}

	public function get_params( $query = null)
	{

        // Regular expression to match parameter keys
		$pattern = '/:[a-zA-Z0-9_]*/';

        // Find all parameter keys in the query string
		preg_match_all($pattern, $query, $matches);

        // Get parameter keys as array
		return $matches[0];

	}


 /**
     * Insert new record into database, as fast as possible, no safety checks, lobs not supported.

  */
 public function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false) {

 	$fields = implode(',', array_keys($params));
 	$qms    = array_fill(0, count($params), '?');
 	$qms    = implode(',', $qms);

 	$params =  array_values($params);

 	$sql = "INSERT INTO {{$table}} ($fields) VALUES($qms)";

 	return $this->execute($sql,$params);
 }

 /**
   * Insert a record into a table and return the "id" field if required,
   * Some conversions and safety checks are carried out. Lobs are supported.
   * If the return ID isn't required, then this just reports success as true/false.
 */

 public function insert_record($table, $dataobject, $returnid=true, $bulk=false) {
 	$dataobject = (array)$dataobject;
 	$cleaned = array();



 	foreach ($dataobject as $field=>$value) {
 		if ($field === 'id') {
 			continue;
 		}
 		$cleaned[$field] = $value;
 	}

 	if (empty($cleaned)) {
 		return false;
 	}


 	return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
 }


/**
     * Update record in database, as fast as possible, no safety checks, lobs not supported.
*/
public function update_record_raw($table, $params, $bulk=false) {
	$params = (array)$params;

	if (!isset($params['id'])) {
		throw new coding_exception('moodle_database::update_record_raw() id field must be specified.');
	}
	$id = $params['id'];
	unset($params['id']);

	if (empty($params)) {
		throw new coding_exception('moodle_database::update_record_raw() no fields found.');
	}

	$sets = array();
	foreach ($params as $field=>$value) {
		$sets[] = "$field = ?";
	}

        $params[] = $id; // last ? in WHERE condition

        $params = array_values($params);

        $sets = implode(',', $sets);
        $sql = "UPDATE {{$table}} SET $sets WHERE id=?";
        return $this->execute($sql, $params);
    }

/**
   * Update a record in a table
   *
   * $dataobject is an object containing needed data
   * Relies on $dataobject having a variable "id" to
   * specify the record to update
*/
public function update_record($table, $dataobject, $bulk=false) {
	$dataobject = (array)$dataobject;

	$cleaned = array();

	foreach ($dataobject as $field=>$value) {
		if (is_bool($value)) {
                $value = (int)$value; // prevent "false" problems
            }
            $cleaned[$field] = $value;
        }

        return $this->update_record_raw($table, $cleaned, $bulk);
    }


    public function delete_record($table, $select=null, array $params=null) {
    	$sql = "DELETE FROM {{$table}}";
    	if ($select) {
    		$sql .= " WHERE $select";
    	}
    	return $this->execute($sql, $params);
    }


}
?>