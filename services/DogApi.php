<?php

class DogApi extends AggregatorService
{
    /**
     * @return string
     */
    public function get_service()
    {
      return 'dogs';
    }

    /**
     * @return integer
     */
    public function fetch()
    {

        $base_url  = 'https://dog.ceo/api/breeds/list';
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
            foreach ($decoded_result->message as $animal) {
                $item = $this->build_item();
                $item->item = html_entity_decode($animal);
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
