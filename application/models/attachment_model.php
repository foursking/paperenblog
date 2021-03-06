<?php

/**
 * 2012-3-18 17:34:28
 * 附件模型
 * @author paperen<paperen@gmail.com>
 * @link http://iamlze.cn
 * @version 0.0
 * @package paperenblog
 * @subpackage application/models/
 */
class Attachment_model extends CI_Model
{

	/**
	 * 表映射
	 * @var array
	 */
	private $_tables = array(
		'attachment' => 'attachment',
		'user' => 'user',
		'post_attachment' => 'post_attachment',
		'post' => 'post',
	);

	/**
	 * 根據附件ID獲取附件數據
	 * @param int $attachment_id
	 * @return array
	 */
	public function get_by_id( $attachment_id )
	{
		return $this->db->select(
								'a.id,a.name,a.path,
						 a.suffix,a.size,
						 a.isimage,a.isthumbnail,
						 a.addtime,
						 u.name as uploader'
						)
						->from( "{$this->_tables['attachment']} as a" )
						->join( "{$this->_tables['user']} as u", 'u.id = a.userid' )
						->where( 'a.id', $attachment_id )
						->get()
						->row_array();
	}

	/**
	 * 根據文章ID獲取相關的附件數據
	 * @param int $post_id 文章ID
	 * @return array
	 */
	public function get_by_post_id( $post_id )
	{
		return $this->db->select(
								'a.id,a.name,a.path,
						 a.suffix,a.size,
						 a.isimage,a.isthumbnail,
						 a.addtime,
						 u.name as uploader'
						)
						->from( "{$this->_tables['post_attachment']} as pa" )
						->join( "{$this->_tables['attachment']} as a", 'a.id = pa.attachmentid' )
						->join( "{$this->_tables['user']} as u", 'u.id = a.userid' )
						->where( 'pa.postid', $post_id )
						->get()
						->result_array();
	}

	/**
	 * 獲取指定文章IDS的代表圖片
	 * @param array $post_ids 文章IDs
	 * @return array
	 */
	public function get_thumbnail_by_post_ids( $post_ids )
	{
		return $this->db->select(
								'a.id,a.name,a.path,
						 a.suffix,a.size,
						 a.addtime,
						 u.name as uploader,
						 pt.postid'
						)
						->from( "{$this->_tables['attachment']} as a" )
						->join( "{$this->_tables['post_attachment']} as pt", 'pt.attachmentid = a.id' )
						->join( "{$this->_tables['user']} as u", 'u.id = a.userid' )
						->where_in( 'pt.postid', $post_ids )
						->where( 'a.isthumbnail', TRUE )
						->where( 'a.isimage', TRUE )
						->get()
						->result_array();
	}

	/**
	 * 插入附件信息
	 * @param array $data
	 * @return int
	 */
	public function insert( $data )
	{
		$insert_data = array(
			'name' => $data['name'],
			'path' => $data['path'],
			'suffix' => $data['suffix'],
			'size' => $data['size'],
			'isimage' => $data['isimage'],
			'isthumbnail' => $data['isthumbnail'],
			'addtime' => time(),
			'userid' => $data['userid'],
		);
		$this->db->insert( $this->_tables['attachment'], $insert_data );
		return $this->db->insert_id();
	}

	/**
	 * 更新指定附件記錄為特色圖像
	 * @param int $attachment_id
	 */
	public function update_isthumbnail( $attachment_id )
	{
		$update_data = array(
			'isthumbnail' => TRUE,
		);
		$this->db->where( 'id', $attachment_id )
				->update( $this->_tables['attachment'], $update_data );
		return $this->db->affected_rows();
	}

	/**
	 * 根據附件ID刪除相應的附件數據
	 * @param int $ids
	 * @return int 影響行數
	 */
	public function delete_by_ids( $ids )
	{
		$this->db->where_in( 'id', $ids )
				->delete( $this->_tables['attachment'] );
		return $this->db->affected_rows();
	}

	/**
	 * 解除文章與附件的關係
	 * @param int $post_id
	 * @return int 影響行數
	 */
	public function delete_by_post_id( $post_id )
	{
		$this->db->where( 'postid', $post_id )
				->delete( $this->_tables['post_attachment'] );
		return $this->db->affected_rows();
	}

	/**
	 * 某个用户上传的附件总数
	 * @param int $user_id
	 * @return int 总数
	 */
	public function total_by_userid( $user_id )
	{
		return $this->db->where( 'userid', $user_id )
						->count_all_results( $this->_tables['attachment'] );
	}

	/**
	 * 上传的附件总数
	 * @return int 总数
	 */
	public function total()
	{
		return $this->db->count_all_results( $this->_tables['attachment'] );
	}

	/**
	 * 获取某个用户上传文件的总大小
	 * @param int $user_id
	 * @return float
	 */
	public function total_size_by_userid( $user_id )
	{
		return $this->db->select_sum( 'size' )
						->where( 'userid', $user_id )
						->from( $this->_tables['attachment'] )
						->get()
						->row_array();
	}

	/**
	 * 获取上传文件的总大小
	 * @return float
	 */
	public function total_size()
	{
		return $this->db->select_sum( 'size' )
						->from( $this->_tables['attachment'] )
						->get()
						->row_array();
	}

	/**
	 * 获取上传的附件
	 * @return array
	 */
	public function get_all( $per_page = 0, $offset = 0 )
	{
		$query = $this->db->select(
						'a.id,a.name,a.path,a.suffix,a.size,a.addtime,a.isthumbnail,a.isimage,
						 u.name as uploader,
						 pt.postid,
						 p.urltitle,p.title'
				)
				->from( "{$this->_tables['attachment']} as a" )
				->join( "{$this->_tables['post_attachment']} as pt", 'pt.attachmentid = a.id' )
				->join( "{$this->_tables['user']} as u", 'u.id = a.userid' )
				->join( "{$this->_tables['post']} as p", 'p.id = pt.postid' )
				->order_by( 'a.addtime', 'desc' );
		$query->limit( $per_page, $offset );
		return $query->get()->result_array();
	}

	/**
	 * 获取某个用户上传的附件
	 * @param int $user_id
	 * @return array
	 */
	public function get_by_authorid( $user_id, $per_page = 0, $offset = 0 )
	{
		$query = $this->db->select(
						'a.id,a.name,a.path,a.suffix,a.size,a.addtime,a.isthumbnail,a.isimage,
						 u.name as uploader,
						 pt.postid,
						 p.urltitle,p.title'
				)
				->from( "{$this->_tables['attachment']} as a" )
				->join( "{$this->_tables['post_attachment']} as pt", 'pt.attachmentid = a.id' )
				->join( "{$this->_tables['user']} as u", 'u.id = a.userid' )
				->join( "{$this->_tables['post']} as p", 'p.id = pt.postid' )
				->where( 'a.userid', $user_id )
				->order_by( 'a.addtime', 'desc' );
		$query->limit( $per_page, $offset );
		return $query->get()->result_array();
	}

}

// end of Post_model