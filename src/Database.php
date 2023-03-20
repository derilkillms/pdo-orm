<?php
/**
 * Author   : Muhammad Deril
 * URI      : http://www.derillab.com
 * Github   : @derilkillms
 */

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

	
}



?>