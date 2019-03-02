<?php

namespace App\Providers;

use Illuminate\View\Compilers\BladeCompiler;

// Encryption 모듈에서 암호화된 텍스트를 블레이드 뷰에서 자연스럽게 읽을 수 있도록 Blade Compiler에서 use 하는 \Illuminate\View\Compilers\Concerns\CompilesEchos trait에 있는 함수들 Override

class ManapieBladeCompiler extends BladeCompiler {

    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            
            if($matches[1]){
				$match=substr($matches[0], 1);
				$wrapped=\App\Encryption::checkEncrypted($match)?\App\Encryption::decrypt($match):$match;
				
	            return $match;
	            
            }else{
            	$whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            	
            	$text=\App\Encryption::checkEncrypted($matches[2])?\App\Encryption::decrypt($matches[2]):$matches[2];
				$wrapped=$this->compileEchoDefaults($text);
	            	
	            return "<?php echo({$wrapped}); ?>{$whitespace}";
            }
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
    
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            
            if($matches[1]){
				$match=substr($matches[0], 1);
				$wrapped=\App\Encryption::checkEncrypted($match)?\App\Encryption::decrypt($match):$match;
				
	            return $match;
	            
            }else{
	            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
	
	            $wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));
	            	
	            return "<?php echo((\App\Encryption::checkEncrypted($wrapped)?\App\Encryption::decrypt($wrapped):$wrapped)); ?>{$whitespace}";
            }
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
    
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            
            if($matches[1]){
				$match=substr($matches[0], 1);
				$wrapped=\App\Encryption::checkEncrypted($match)?\App\Encryption::decrypt($match):$match;
				
	            return $match;
	            
            }else{
	            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
	            
            	$text=\App\Encryption::checkEncrypted($matches[2])?\App\Encryption::decrypt($matches[2]):$matches[2];
				$wrapped=$this->compileEchoDefaults($text);
	            	
	            return "<?php echo(e({$wrapped})); ?>{$whitespace}";
	        }
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
}