<?php

//2013.11.11 - Jesse L Quattlebaum (psyjoniz@gmail.com)
//quickly thrown together mysql db handler class

class MySQL {

	public  $sDBHost = 'localhost';
	public  $sDBUser = 'root';
	public  $sDBPass = '';
	public  $sDBData = 'demo';
	private $dbHand  = false;
	private $sSQL    = '';
	private $qResults;
	private $iInsertId;

	/**
	 * constructor with a-la-carte config
	 *
	 * @param array $aConfig allowing overide of hardcoded configuration parameters in an a-la-carte fasion for the following : sDBHost, sDBUser, sDBPass, sDBData and sSQL
	 * @return boolean|array
	 */
	function __construct($aConfig) {
		if(isset($aConfig['sDBHost']))
			$this->sDBHost = $aConfig['sDBHost'];
		if(isset($aConfig['sDBUser']))
			$this->sDBUser = $aConfig['sDBUser'];
		if(isset($aConfig['sDBPass']))
			$this->sDBPass = $aConfig['sDBPass'];
		if(isset($aConfig['sDBData']))
			$this->sDBData = $aConfig['sDBData'];
		$this->connect();
		if(isset($aConfig['sSQL']))
			return $this->query($aConfig['sSQL']);
		return true;
	}

	/**
	 * connect to database
	 *
	 * @return boolean|resource
	 */
	function connect()
	{
		if(!$this->dbHand)
		{
			if(!$this->dbHand = mysql_connect($this->sDBHost, $this->sDBUser, $this->sDBPass))
			{
				throw new Exception('ERROR:DB_CONNECTION_FAILED:"' . $this->sDBUser . '@`' . $this->sDBHost . '`.`' . $this->sDBData . '`"');
				return false;
			}
			if(!mysql_select_db($this->sDBData, $this->dbHand))
			{
				throw new Exception('ERROR:DB_SELECTION_FAILED:"' . $this->sDBUser . '@`' . $this->sDBHost . '`.`' . $this->sDBData . '`"');
				return false;
			}
		}
		return $this->dbHand;
	}

	/**
	 * nothing fancy
	 *
	 * @param string $sInput text to be escaped
	 * @return string
	 */
	function escapeString($sInput) {
		return mysql_real_escape_string($sInput, $this->dbHand);
	}

	/**
	 * talk with the database
	 *
	 * @param string $sSQL query statement to send to the database
	 * @param boolean $bReturnResults switch to return or not return found results
	 * @return boolean|array
	 */
	function query($sSQL, $bReturnResults = true) // lightweight db handling -- great for gotta-have-it-now horrible for after it starts taking off and you gotta *cough* expand
	{
		$aSQLParts = split(' ', trim($sSQL));
		$sSQLStart = strtolower($aSQLParts[0]);
		$this->connect();
		if(!$this->dbHand)
		{
			throw new Exception('ERROR:DB_CONNECTION_FAILED:"' . $this->sDBUser . '@`' . $this->sDBHost . '`.`' . $this->sDBData . '`"');
			return false;
		}
		$this->qResults = mysql_query($sSQL, $this->dbHand);
		if(false === $this->qResults)
		{
			throw new Exception('ERROR:DB_QUERY_FAILED:"' . mysql_error() . '" -- SQL:"' . $sSQL . '"');
			return false;
		}
		$this->iInsertId = @mysql_insert_id();
		if($bReturnResults) // only build and return results if we were looking for them
		{
			if($sSQLStart != 'insert' && $sSQLStart != 'update')
			{
				while($aResult = mysql_fetch_array($this->qResults, MYSQL_ASSOC))
					$aResults[] = $aResult; // yes, always return a multi-dimensional array, even if its just one element -- this makes for post-processing ease
				if(count($aResults) == 0)
					throw new Exception('NOTICE:NO_RESULTS_FOUND');
			}
			//$this->iTotal = $this->getQueryTotal(); // somehow this broke flagging, NO idea how...  but flagging goes apeshit when this is enabled
			return $aResults;
		}
		else // if we got here, guesstimate we were successful
			return true;
	}

	/**
	 * get the most recent inserted records id
	 *
	 * @return interger
	 */
	function getInsertId() {
		return $this->iInsertId;
	}
}
