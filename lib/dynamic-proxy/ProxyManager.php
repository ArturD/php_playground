<?php
/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-04-20
 * @version 1.0 2009-04-20
 */

require_once 'simple/ClassSIP.php';

class ProxyManager {
	private static $generated = array();

	const INTERFACE_SUFFIX = '_PIClass';
	const CLASS_SUFFIX = '_PCClass';
	const HANDLER_FIELD_NAME = 'proxyHandlerObject';
	const CALL_PARENT_METHOD_NAME = 'CallParentMethod_PF';
	
	public static function GetInterfaceProxyObject(ISimpleInvocationProxy $handler, $classOrInterface) {
		$newClassName = $classOrInterface . self::INTERFACE_SUFFIX;
		if (empty(self::$generated[$newClassName])) self::GenerateInterfaceProxy($classOrInterface, true);
		return new $newClassName($handler);
	}
	
	public static function GetClassProxyObject(ISimpleInvocationProxy $handler, $class, array $args = array()) {
		$newClassName = $class . self::CLASS_SUFFIX;
		
		$classSIP = new ClassSIP();
		$chainHandler = new ChainSIP($handler, $classSIP);

		if (empty(self::$generated[$newClassName])) self::GenerateClassProxy($class, true);
		$obj = new $newClassName($chainHandler, $args);
                return $obj;
	}
	
	public static function GenerateInterfaceProxy($classOrInterface, $eval = true) {
		$eval = (bool) $eval;
		$result = self::generateClass($classOrInterface, $eval, false);
		return $result;
	}
	
	public static function GenerateClassProxy($classOrInterface, $eval = true) {
		$eval = (bool) $eval;
		$result = self::generateClass($classOrInterface, $eval, true);
		return $result;
	}
	
	private static function generateClass($classOrInterface, $eval, $realClass) {
		$newClassName = $classOrInterface . (($realClass) ? self::CLASS_SUFFIX : self::INTERFACE_SUFFIX);
		if (empty(self::$generated[$newClassName])) {
			if (class_exists($newClassName)) {
				throw new Exception("Class cannot be created! Class named '$newClassName' already exists!");
			}
			if (class_exists($classOrInterface) || (interface_exists($classOrInterface) && $realClass == false)) {
				$reflection = new ReflectionClass($classOrInterface);
				self::generateClassCode($reflection, $realClass);
			} else {
				$ci = ($realClass) ? 'class' : 'class/interface';
				throw new InvalidArgumentException("A $ci '$classOrInterface' does not exist!");
			}
		}
		
		if (class_exists($newClassName)) {
			$eval = false;
		}
		
		$code = self::$generated[$newClassName];
		if ($eval == true) eval($code);
		return $code;
        }

        private static function getConstructorCall(ReflectionClass $reflection) {
          $constructor = $reflection->getConstructor();
          $n = $constructor->getNumberOfParameters();
          $str = "parent::__construct(";
          for( $i=0; $i<$n; $i++) {
            if( $i > 0 ) $str .= ",";
            $str .= "\$args[" . $i . "]";
          }
          $str .= ");";
          return $str;
        }
	
	private static function generateClassCode(ReflectionClass $reflection, $realClass) {
		$name = $reflection->getName();
		$methodReflections = $reflection->getMethods();
		
		$suffix = ($realClass) ? self::CLASS_SUFFIX : self::INTERFACE_SUFFIX;
		
		$newClassName = $name . $suffix;
		
		$relation = ($reflection->isInterface()) ? ' implements ' : ' extends ';
		
		$code = 'class ' . $newClassName . $relation . $name . ' {';
						
		//Create the constructor and the needed protected field
		$code .='
	protected $' . self::HANDLER_FIELD_NAME . ';';
		
		if ($realClass) {
			$code .='	
	public function __construct(ISimpleInvocationProxy $handler, array $args) {
                $this->' . self::HANDLER_FIELD_NAME . ' = $handler;
                '.self::getConstructorCall($reflection).'
		//$this->' . self::CALL_PARENT_METHOD_NAME . '(\'__construct\', $args);
	}
';
		} else {
			$code .='	
	public function __construct(ISimpleInvocationProxy $handler) {
		$this->' . self::HANDLER_FIELD_NAME . ' = $handler;
	}
';
		}

	if ($realClass) $code .= '
	public function &' . self::CALL_PARENT_METHOD_NAME . '($method, array $args) {
		$errorLevel = error_reporting(E_ERROR);
		
		$code = \'error_reporting(\' . $errorLevel. \'); $result =& parent::\' . $method . \'(\';
		foreach ($args AS $k => $v) {
			$code .= \'&$args[\' . $k . \'],\';
		}
		if (count($args) > 0) $code = substr($code, 0, -1);
		$code.= \');\';

		eval($code);
		return $result;
	}
';
			
		foreach ($methodReflections AS $mr) {
			$code .= self::generateMethodCode($mr);
			$code .= "\r\n";
		}
		
		$code .= '}';
			
                self::$generated[$newClassName] = $code;
	}
	
	private static function generateMethodCode(ReflectionMethod $method) {
		$code = '';
		
		if ($method->isPublic() && $method->isConstructor() == false && $method->isDestructor() == false && $method->isFinal() == false && $method->isStatic() == false) {
			$code .= "\t"; 
			$code .= 'public ';
			if ($method->isStatic()) $code .= 'static ';
			$code .= 'function ';
			if ($method->returnsReference()) $code .= '&';
			$code .= $method->getName();
			$code .= '(';
					
			$paramaterReflections = $method->getParameters();
			
			$paramRefs = '';
			foreach ($paramaterReflections AS $pr) {
				$code .= self::generateParameterCode($pr);
				$code .= ',';
				$paramRefs .= ',&$' . $pr->getName();
			}
			$paramRefs = substr($paramRefs, 1);
			$paramsCount = count($paramaterReflections);
			
			if ($paramsCount > 0) $code = substr($code, 0, -1);
					
			$code .= ') {
		$args = array(' . $paramRefs . ');
		$count = func_num_args();
		for ($i=' . ($paramsCount) . '; $i < $count; $i++) {
			$args[] = func_get_arg($i);
		}
		return $this->' . self::HANDLER_FIELD_NAME . '->InvokeMethod($this, \'' . $method->getName() . '\', $args, new ChainSIP());
	}';			
		}
		
		return $code;
	}
	
	private static function generateParameterCode(ReflectionParameter $parameter) {
		$code = $hint = self::getTypeHinting($parameter);
		
		$code .= ' ';

		if ($parameter->isPassedByReference()) $code .= '&';
		
		$code .= '$';
		$code .= $parameter->getName();
		
		if ($parameter->isDefaultValueAvailable()) {
			$code .= '=' . str_replace("\n",'',var_export($parameter->getDefaultValue(), true));
		} else if (empty($hint) == false && $parameter->allowsNull()) {
			$code .= '=null'; 
		}
		
		return $code;
	}
	
	private static function getTypeHinting(ReflectionParameter $parameter) {
		$param = (string) $parameter;
		$start = strpos($param, '>') + 1;
		$end = strpos($param,'$');
		if ($parameter->isPassedByReference()) $end--;
		if ($parameter->allowsNull() && $end-8 > $start) $end -= 8;
		$param = substr($param, $start, $end-$start);
		return trim($param);
	}
}
?>
