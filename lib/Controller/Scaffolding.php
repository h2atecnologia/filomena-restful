<?php

namespace RESTful\Controller;

abstract class Scaffolding
{
	protected $class_name = null;

	public function Get($id = null, $_get = null)
	{
		if(!empty($id) && !is_numeric($id))
			$this->bad_request("wrong id");

		$_model = $this->class_name;

		if($id != null)
		{
			try
			{
				$row = $_model::find($id);

				$this->render_json($row->attributes());

			} catch (\Exception $e) {
				
				$this->bad_request( $this->getFriendlyMessage($e->getMessage(), "read failure") );
			}
		}

		$rows = $_model::find("all");

		$_temp = array();
		
		foreach($rows as $row)
		{
			$_temp[] = $row->attributes();
		}
		
		$this->render_json($_temp);
	}

	public function Post($_post = null)
	{
		if($_post == null)
			$this->bad_request("post data is missing");

		try
		{
			$_model = $this->class_name;

			$row = $_model::create((array)$_post);

		} catch (\Exception $e) {
		
			$this->bad_request( $this->getFriendlyMessage($e->getMessage(), "create failure") );
		}
	}

	public function Put($id = null, $_put = null)
	{
		if(!empty($id) && !is_numeric($id))
			$this->bad_request("wrong id");

		if($_put == null)
			$this->bad_request("put data is missing");

		try
		{
			$_model = $this->class_name;
		
			$row = $_model::find($id);

			$row->update_attributes((array)$_put);

		} catch (\Exception $e) {

			$this->bad_request( $this->getFriendlyMessage($e->getMessage(), "update failure") );
		}
	}

	public function Delete($id = null)
	{
		if(empty($id) || !is_numeric($id))
			$this->bad_request("wrong id");

		try
		{
			$_model = $this->class_name;
			
			$row = $_model::find($id);
			
			if(!$row->delete())
				$this->bad_request("delete failure");
				
		} catch (\Exception $e) {

			$this->bad_request( $this->getFriendlyMessage($e->getMessage(), "delete failure") );
		}
	}

	protected function getFriendlyMessage($message, $default = "unknown failure")
	{
		if(preg_match("/Couldn't find[.]*/i", $message))
			return "id not found";
		else if(preg_match("/Duplicate entry[.]*/i", $message))
			return "duplicate entry/key";
		else
			return $default;
	}	
}

?>