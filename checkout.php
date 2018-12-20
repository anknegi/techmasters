<?php 

interface IDiscount {
  public function apply(&$amount);
}

class Discount_FlatOff implements IDiscount {
  public function apply(&$amount) {
    $amount -= 50;
  }
}

class Discount_FixedAmount implements IDiscount {
  private $amount;

  public function __construct($amount) {
    $this->amount = $amount;
  }

  public function apply(&$amount) {
    $amount -= $this->amount;
  }
}

class Product {
  private $discounts = [];

  public function __construct($sku, $name,  $price) {
    $this->item  = $sku;
    $this->name = $name;
    $this->price = $price;
  }

  public function addDiscount(IDiscount $discount) {
    $this->discounts[] = $discount;
  }

  public function getPrice() {
    $price = $this->price;
    foreach($this->discounts as $discount)
      $discount->apply($price);
    return $price;        
  }
}

class Checkout {
  private $contents  = [];
  private $discounts = [];
  private $count = [];

  public function addDiscount(IDiscount $discount) {
    $this->discounts[] = $discount;
  }

  public function scan(Product $product) {
    $this->contents[] = $product;    
  }

  public function getCount() {
   
    foreach($this->contents as $items)
    {
      $count[$items->item] ++ ;
    }
    
    return $count;
  }

  public function getTotalCount() {

  	return count($this->contents);
  }

  public function getTotal() {
    $ttl = 0;
   
    foreach($this->contents as $product)
      $ttl += $product->getPrice();

    foreach($this->discounts as $discount)
      $discount->apply($ttl);

    return $ttl;
  }
}

$ipd = new Product('ipd','Super Ipad', 549.99 );
$mbp = new Product('mbp','MacBook Pro', 1399.99 );
$atv = new Product('atv','Apple TV', 109.50);
$vga = new Product('vga','VGA adapter', 30.00 );

//$sandwich->addDiscount(new Discount_HalfOff());

$co = new Checkout();

/*
rules : 
1) if you buy 3 units of apple, then you get 1 unit for free
2) if you buy more than 4 units of super ipad then get it at 499
3) vga is free with every mackbook pro
*/

/* 
use case 1 

$co->scan($atv);
$co->scan($atv);
$co->scan($atv);
$co->scan($vga);

*/ 

/* 
use case 2

$co->scan($atv);
$co->scan($ipd);
$co->scan($ipd);
$co->scan($atv);
$co->scan($ipd);
$co->scan($ipd);
$co->scan($ipd);

*/ 

/* 
use case 3

$co->scan($mbp);
$co->scan($vga);
$co->scan($ipd);
 
*/

$atvlimit = 3;
$ipdlimit = 4; 
$flatoff= 50 ; 

$count=  $co->getCount();

if($count['atv'] > $atvlimit)
{
  $co->addDiscount(new Discount_FixedAmount($atv->getPrice())); 
}
elseif($count['ipd'] > $ipdlimit)
{ 
  for($ipdcount=0; $ipdcount < $count['ipd']; $ipdcount ++)
  {
   $co->addDiscount(new Discount_FlatOff($ipd->getPrice())); 
  }
   
}
elseif($count['mbp'] > 0) 
{
  for($vgacount=0; $vgacount < $count['vga']; ++$vgacount)
  {
     if($vgacount ===  $count['mbp'])
    {
      break; 
    }

    $co->addDiscount(new Discount_FixedAmount($vga->getPrice())); 

   
  }
}

echo $co->getTotal();

?>