<?php namespace ZackKitzmiller;

class Tiny {

    protected $set = null;

    public function __construct($set) {
        $this->set = $set;
    }

    public function to($id)
    {
        $set = $this->set;

        $hexn = '';
        $id = floor(abs(intval($id)));
        $radix = strlen($set);
        while (true) {
            $r = $id % $radix;
            $hexn = $set[$r] . $hexn;
            $id = ($id - $r) / $radix;
            if ($id == 0) {
                break;
            }
        }
        return $hexn;
    }

    public function from($str)
    {
        $set = $this->set;

        $radix = strlen($set);
        $strlen = strlen($str);
        $n = 0;
        for ($i = 0; $i < $strlen; $i++) {
            $n += strpos($set, $str[$i]) * pow($radix, ($strlen - $i - 1));
        }
        return $n;
    }

    public static function generate_set()
    {
        $arr = array();

        for ($i = 65; $i <= 122; $i++) {
            if ($i < 91 || $i > 96) $arr[] = chr($i);
        }

        $arr = array_merge($arr, range(0, 9));
        shuffle($arr);

        return join('', $arr);
    }

}
