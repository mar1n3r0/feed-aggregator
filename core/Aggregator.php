<?php
include('services/DogApi.php');
include('services/RecipePuppyApi.php');

/**
 * Class Aggregator
 */
class Aggregator
{
    /**
     * @var array
     */
    private static $services = array();

    /**
     * @param $class
     */
    public static function register($class)
    {
        self::$services[] = array('class' => $class);
    }

    /**
     * @var
     */
    private $db;

    /**
     * Aggregator constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->filter = array();
    }

    /**
     * Instantiate all Service classes and fetch the data
     */
    public function fetch()
    {
        foreach (self::$services as $service) {
            $class = $service['class'];
            $instance = new $class($this->db);
            $instance->fetch();
        }
    }

    /**
     * @param $service
     * @return $this
     */
    public function service($service)
    {
        $this->filter['service'] = $service;
        return $this;
    }

    /**
     * @param $from_date
     * @return $this
     */
    public function from_date($from_date)
    {
        $this->filter['from_date'] = $from_date;
        return $this;
    }

    /**
     * @param $to_date
     * @return $this
     */
    public function to_date($to_date)
    {
        $this->filter['to_date'] = $to_date;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function limit($offset)
    {
        $this->filter['offset'] = $offset;
        return $this;
    }

    /**
     * @return $this
     */
    public function count()
    {
        $this->filter['count'] = true;
        return $this;
    }

    /**
     * @return string
     */
    public function query()
    {
        $filter = $this->filter;
        $filter_params = array('service');
        if (isset($filter['from_date'])) {
            $from = $filter['from_date'];
        }
        if (isset($filter['to_date'])) {
            $to = $filter['to_date'];
        }

        $conditions = array();
        foreach ($filter_params as $fp) {
            if (isset($filter[$fp])) {
                $check = $filter[$fp];
                if (is_array($check)) {
                    $conditions[] = $fp . ' IN(' . array_map(array($this, 'quote_string'), $check) . ')';
                } else {
                    $conditions[] = $fp . ' = ' . $this->quote_string($check);
                }
            }
        }
        if (isset($filter['count'])) {
            $sql = "SELECT count(*) as count FROM aggregate";
        } else {
            $sql = "SELECT * FROM aggregate";
        }
        if (count($conditions)) $sql .= " WHERE " . implode(' AND ', $conditions);
        if ($from && !$to) {
            $sql .= " and created_at > cast('$from' as datetime)";
        }
        if (!$from && $to) {
            $sql .= " and created_at < cast('$to' as datetime)";
        }
        if ($from && $to) {
            $sql .= " AND created_at between cast('$from' as datetime) and cast('$to' as datetime)";
        }
        $limit = isset($filter['offset']) ? abs((int) $filter['offset']) : 30;
        if (!$filter['count']) {
            $sql .= " ORDER BY created_at DESC LIMIT $limit";
        }
        if ($res = mysqli_query($this->db, $sql)) {
            $items = array();

            if (mysqli_num_rows($res) > 0) {
              while ($item = mysqli_fetch_object($res, 'Aggregatoritem')) {
                  $items[] = $item;
              }
              if (isset($items[0]->count)) {
                  // if count is called and returns 0 results
                  if ($items[0]->count == 0) {
                      $out = array('error'=> true,
                          'message' => 'No data matching your criteria found.',
                          'data' => array());
                  } else {
                      $out = array('error'=>false,'message'=>'','data'=> array('totalItems' => $items[0]->count));
                  }
              } else {
                  $out = array('error'=>false,'message'=>'','data'=> $items);
              }
            } else {
                  $out = array('error'=> true,
                           'message' => 'No data matching your criteria found.',
                           'data' => array());
            }
        } else {
              $out = array('error'=> true,
                         'message' => 'No data matching your criteria found.',
                         'data' => array());
        }
        header('Content-Type: application/json');
        return json_encode($out, JSON_PRETTY_PRINT);
    }

    /**
     * @param $str
     * @return string
     */
    public function quote_string($str)
    {
        return "'" . mysqli_real_escape_string($this->db, $str) . "'";
    }
}

/**
 * Class AggregatorItem
 */
class AggregatorItem
{
    /**
     * @var null
     */
    public $service                 = null;
    /**
     * @var null
     */
    public $item                    = null;

    /**
     * @param $db
     * @return bool
     */
    public function save($db)
    {
          $sql = sprintf("
              INSERT INTO aggregate
                  (service, item)
              VALUES
                  ('%s', '%s')
            ",  mysqli_real_escape_string($db, $this->service),
                mysqli_real_escape_string($db, $this->item)
            );
          if (mysqli_query($db, $sql)) {
              return true;
          } else {
              echo "Error saving item: " . mysqli_error($db);
              return false;
          }
    }
}

/**
 * Class AggregatorService
 */
abstract class AggregatorService
{
    /**
     * @var
     */
    protected $db;

    /**
     * AggregatorService constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @return mixed
     */
    public abstract function get_service();

    /**
     * @return mixed
     */
    public abstract function fetch();

    /**
     * @return AggregatorItem
     */
    protected function build_item()
    {
        $item = new Aggregatoritem;
        $item->service = $this->get_service();
        return $item;
    }
}

?>
