<?php namespace ZackKitzmiller\Tiny;

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
            $hexn = $set{$r} . $hexn;
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
            $n += strpos($set, $str{$i}) * pow($radix, ($strlen - $i - 1));
        }
        return $n;
    }

}
