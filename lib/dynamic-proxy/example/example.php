<?php
require_once '../ProxyManager.php';
require_once '../simple/TargetSIP.php';

//A dynamic proxy is a class created at runtime and based on another class or interface.
//It has all public, non-static, non-final methods that the base class/interface has with the same signature.
//All those methods call a single method in a handler (instanceof ISimpleInvocationProxy (SIP)) given in the constructor of the proxy.
//This way you can centralize such common code as logging, caching, etc.

//There are 4 main implementators of the ISimpleInvocationProxy interface
/*
TargetSIP
Accepts one argument in the constructor which must be object (the target).
InvokeMethod method invokes the method on the target ($target->methodName($arg1,$arg2...)). 

ClassSIP
InvokeMethod method invokes the parent class' method.
It's automatically set as the final SIP when you use GetClassProxyObject method.
MUST NOT BE USED WHIT INTERFACE PROXY!

ChainSIP
Accepts any number of arguments in the constructor. They must be SIPs.
Tha class is used to create a chain of handlers.
InvokeMethod method invokes the method on the first SIP in the chain.
InvokeNext method invokes the method on the next SIP.

NullChainSIP
A ChainSIP that always returns null and has no SIPs in it.
*/

//And here is an example to show the use of Dynamic Proxies.

//Any class you have
class MyClass {
	public $a;
	
	public function __construct($a) {
		$this->a = $a;
	}
	
	public function myF1() {
		usleep(rand(100*1000, 200*1000)); //sleep for 100-200ms
	}
	
	public function myF2() {
		$this->myF1();
		usleep(rand(300*1000, 500*1000)); //sleep for 300-500ms
	}
	
	public function GetA() {
		return $this->a;
	}
}

//A very simple PerformanceSIP
class PerformanceSIP implements ISimpleInvocationProxy {
	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
		$start = microtime(true);
		
		//call the next SIP (which will eventually call the real method)
		$result =& $chain->InvokeNext($proxy, $methodName, $args); 
		//You must get this as reference for the real method to work properly!.
		
		$end = microtime(true);
		$time = $end-$start;
		$time*=1000;
		echo "$methodName - $time msec.<br/>";
		
		return $result;
	}
}

//There are 2 types of proxies.
//1.An interface proxy
//This can be based either on a class or an interface.
//If it's based on a class, it's fields are never used and the parent methods cannot be called.
//This type of proxy just sends all method calls to the InvokeMethod method of the handler.

//In this type of proxy you must create a SIP that does the actual work of the method.
$target = new MyClass('TATATA');
$handler = new ChainSIP(new PerformanceSIP(), new TargetSIP($target));
//This creates a chain of handlers.
//When you call a method on the object below it'll first call PerformanceSIP's InvokeMethod
//And then it will call TargetSIP's InvokeMethod which will invoke the method on an object $a;

//Creates an object of the Interface Proxy based on the class MyClass with the handler $handler
$intProxy = ProxyManager::GetInterfaceProxyObject($handler, 'MyClass');
$intProxy->MyF1();
$intProxy->MyF2();
$intProxy->MyF1();
echo $intProxy->GetA(), '<br/>'; //'TATATA'

//$intProxy is instance of class that extends MyClass but it's only used to intercept the method calls.
//If you change it's field a, nothing will happen.

$intProxy->a = 'Changed';
echo $intProxy->GetA(), '<br/>'; //'TATATA'

//2.A class proxy
//This can only be based on a class.
//In this type of proxy the fields of the class are actually used.

//The last argument is an array with the arguments needed by the constructor of the class.
$clsProxy = ProxyManager::GetClassProxyObject(new PerformanceSIP(), 'MyClass', array('TATATA'));
//This creates a new ChainSIP with the handler you give as first argument and a ClassSIP.

$clsProxy->MyF1();
$clsProxy->MyF2();
$clsProxy->MyF1();
echo $clsProxy->GetA(), '<br/>'; //'TATATA'
$clsProxy->a = 'Changed';
echo $clsProxy->GetA(), '<br/>'; //'Changed'

//There are methods for getting the code of the generated class
//The first parameter is the class or interface name
//The second is a boolean value. If true, the code will be eval()'ed
$intCode = ProxyManager::GenerateInterfaceProxy('MyClass', true);
$clsCode = ProxyManager::GenerateClassProxy('MyClass', true);

//Dynamic proxies illustrate the basics of Aapect-Oriented Programming.
//Proxies are just one way that aspect-oriented behavior or crosscutting concerns can be added dynamically to your code.
?>