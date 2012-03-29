<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Querycache
 *
 * @author paperen
 */
class Querycache
{

	private $_CI;

	private $_cache;

	function __construct()
	{
		$this->_CI =& get_instance();
	}

	/**
	 *
	 * @param string $model 模型名(不需要加_model)
	 * @param string $method 方法
	 * @return mixed
	 */
	public function get( $model, $method )
	{
		// 获取参数数据
		$args = func_get_args();
		$model_and_method = array_shift( $args ) . '_' . array_shift( $args );

		// 索引
		$hash = "{$model_and_method}_" . trim( implode( '_', $args ), '_' );

		// 存在缓存直接返回
		if ( isset( $this->_cache[$hash] ) ) return $this->_cache[$hash];

		// 通过模型获取数据
		$model_name = "{$model}_model";
		if ( !isset( $this->_CI->$model_name ) ) $this->_CI->load->model( $model_name );
		$result = call_user_func_array( array( $this->_CI->$model_name, $method ), $args );

		// 缓存起来
		$this->_cache[$hash] = $result;
		return $result;
	}

}