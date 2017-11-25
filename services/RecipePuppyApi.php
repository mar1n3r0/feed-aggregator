<?php

class RecipePuppyApi extends AggregatorService
{
    /**
     * @return string
     */
    public function get_service()
    {
      return 'recipepuppyapi';
    }

    /**
     * @return integer
     */
    public function fetch()
    {

        $base_url  = 'http://www.recipepuppy.com/api/';
        $this->fetch_by_query($base_url);

    }

    private function fetch_by_query($url)
    {
        $count = 0;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $decoded_result = json_decode($result);
        curl_close($ch);
        if ($decoded_result !== false) {
            foreach ($decoded_result->results as $recipe) {
                $item = $this->build_item();
                $item->item = json_encode($recipe);

                if ($item->save($this->db)) {
                    $count++;
                }
            }
        } else {
            echo 'The feed is down. Try again later.';
            die();
        }
        return $count;
    }
}

?>
