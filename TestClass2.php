<?php

class TestSmall {
	

	/**
     * Get data from entry_id from the seo_translations table
     * @param $id
     * @return mixed
     */
    public function submit_seo_statistics($insert_array)
    {
		$this->get_seo_translation($id);
    }
	
	/**
     * Get data from entry_id from the seo_translations table
     * @param $id
     * @return mixed
     */
    public function get_seo_translation($id)
    {

        $result = $this->db->get();
		$this->deep2($insert_array);
        return $result;
    }
	
	public function deep2($insert_array) {
		$this->deep3();
    }
	
	public function deep3($insert_array11) {
		echo 'hello!';
    }
	
}