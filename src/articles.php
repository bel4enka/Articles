<?php
/**
 * ������
 *
 * ���������� ������
 *
 * @version 2.16
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @copyright 2010, ��� "��� �����", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author ������ ������������ <mihalych@vsepofigu.ru>
 * @author ����� <bersz@procreat.ru>
 * @author ������ ����������
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package Articles
 *
 * $Id$
 */


/**
 * ����� �������
 *
 * @package Articles
 */
class TArticles extends TListContentPlugin
{
	/**
	 * ����� �����: ���� ��������
	 * @var int
	 */
	const BLOCK_NONE = 0;

	/**
	 * ����� �����: ��������� ������
	 * @var int
	 */
	const BLOCK_LAST = 1;

	/**
	 * ����� �����: ��������� ������
	 * @var int
	 */
	const BLOCK_MANUAL = 2;

	/**
	 * ��� �������
	 * @var string
	 */
	public $name = 'articles';

	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.14';

	/**
	 * ��� �������
	 * @var string
	 */
	public $type = 'client,content,ondemand';

	/**
	 * �������� �������
	 * @var string
	 */
	public $title = '������';

	/**
	 * ������ �������
	 * @var string
	 */
	public $version = '2.16a';

	/**
	 * �������� �������
	 * @var string
	 */
	public $description = '���������� ������';

	/**
	 * ��������� �������
	 * @var array
	 */
	public $settings = array(
		'itemsPerPage' => 10,
		'tmplList' => '
			<h1>$(title)</h1>
			$(content)
			$(items)
		',
		'tmplListItem' => '
			<div class="ArticlesListItem">
				<h3>$(caption)</h3>
				$(posted)<br />
				<img src="$(thumbnail)" alt="$(caption)" width="$(thumbnailWidth)" height="$(thumbnailHeight)" />
				$(preview)
				<div class="controls">
					<a href="$(link)">������ �����...</a>
				</div>
			</div>
		',
		'tmplItem' => '
			<div class="ArticlesItem">
				<h1>$(caption)</h1><b>$(posted)</b><br />
				<img src="$(image)" alt="$(caption)" width="$(imageWidth)" height="$(imageHeight)" style="float: left;" />
				$(text)
				<br /><br />
			</div>
		',
		'tmplBlockItem' => '<b>$(posted)</b><br /><a href="$(link)">$(caption)</a><br />',
		'previewMaxSize' => 500,
		'previewSmartSplit' => true,
		'listSortMode' => 'posted',
		'listSortDesc' => true,
		'dateFormatPreview' => DATE_SHORT,
		'dateFormatFullText' => DATE_LONG,
		'blockMode' => 0, # 0 - ���������, 1 - ���������, 2 - ���������
		'blockCount' => 5,
		'THimageWidth' => 120,
		'THimageHeight' => 90,
		'imageWidth' => 640,
		'imageHeight' => 480,
		'imageColor' => '#ffffff',
	);

	/**
	 * ������� ������ ��������
	 * @var array
	 */
	public $table = array (
		'name' => 'articles',
		'key'=> 'id',
		'sortMode' => 'posted',
		'sortDesc' => true,
		'columns' => array(
			array('name' => 'caption', 'caption' => '���������'),
			array('name' => 'posted', 'align'=>'center', 'value'=>templPosted, 'macros' => true),
			array('name' => 'preview', 'caption' => '������', 'maxlength'=>255, 'striptags' => true),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				'create' => array('caption'=>'�������� ������', 'name'=>'action', 'value'=>'create'),
				'list' => array('caption' => '������ ������'),
				'text' => array('caption' => '����� �� ��������', 'name'=>'action', 'value'=>'text'),
			),
		),
		'sql' => "(
			`id` int(10) unsigned NOT NULL auto_increment,
			`section` int(10) unsigned default NULL,
			`active` tinyint(1) unsigned NOT NULL default '1',
			`position` int(10) unsigned default NULL,
			`posted` datetime default NULL,
			`block` tinyint(1) unsigned NOT NULL default '0',
			`caption` varchar(255) NOT NULL default '',
			`preview` text NOT NULL,
			`text` text NOT NULL,
			`image` varchar(255) NOT NULL default '',
			PRIMARY KEY  (`id`),
			KEY `active` (`active`),
			KEY `section` (`section`),
			KEY `position` (`position`),
			KEY `posted` (`posted`),
			KEY `block` (`block`)
		) TYPE=MyISAM COMMENT='Articles';",
	);

	/**
	 * �����������
	 *
	 * ���������� ����������� ������������ �������
	 */
	public function __construct()
	{
		global $Eresus;

		parent::__construct();

		if ($this->settings['blockMode'])
		{
			$Eresus->plugins->events['clientOnPageRender'][] = $this->name;
		}

		$this->table['sortMode'] = $this->settings['listSortMode'];
		$this->table['sortDesc'] = $this->settings['listSortDesc'];

		if ($this->table['sortMode'] == 'position')
		{
			$this->table['controls']['position'] = '';
		}

		if ($this->settings['blockMode'] == self::BLOCK_MANUAL)
		{
			$temp = array_shift($this->table['columns']);
			array_unshift($this->table['columns'], array('name' => 'block', 'align'=>'center',
				'replace' => array(
					0 => '',
					1 => '<span title="������������� � ����� ������">*</span>'
				)
			), $temp);
		}

	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ��������� �������
	 *
	 */
	public function install()
	{
		parent::install();

		umask(0000);
		if (!file_exists($Eresus->fdata . $this->name))
		{
			mkdir($Eresus->fdata . $this->name, 0777);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ��������
	 */
	function updateSettings()
	{
		global $Eresus;

		$item = $Eresus->db->selectItem('`plugins`', "`name`='".$this->name."'");
		$item['settings'] = decodeOptions($item['settings']);
		$keys = array_keys($this->settings);
		foreach ($keys as $key)
		{
			$this->settings[$key] = isset($Eresus->request['arg'][$key]) ?
				$Eresus->request['arg'][$key] : '';
		}

		if ($this->settings['blockMode'])
		{
			$item['type'] = 'client,content';
		}
		else
		{
			$item['type'] = 'client,content,ondemand';
		}
		$item['settings'] = encodeOptions($this->settings);
		$Eresus->db->updateItem('plugins', $item, "`name`='".$this->name."'");
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������ � ��
	 */
	public function insert()
	{
		global $Eresus;

		$item = array();
		$item['section'] = arg('section', 'int');
		$item['active'] = true;
		// FIXME �� ������� position. ������� ���� ������ ����� ����������
		$item['posted'] = gettime();
		$item['block'] = arg('block', 'int');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['text'] = arg('text', 'dbsafe');
		$item['preview'] = arg('preview', 'dbsafe');
		if (empty($item['preview'])) $item['preview'] = $this->createPreview($item['text']);
		$item['image'] = '';

		$Eresus->db->insert($this->table['name'], $item);
		$item['id'] = $Eresus->db->getInsertedID();

		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$tmpFile = $Eresus->fdata . $this->name . '/uploaded.bin';
			upload('image', $tmpFile);

			$item['image'] = $item['id'].'_'.time();
			$filename = $Eresus->fdata . 'articles/'.$item['image'];
			useLib('glib');
			thumbnail($tmpFile, $filename.'.jpg', $this->settings['imageWidth'],
				$this->settings['imageHeight'], $this->settings['imageColor']);
			thumbnail($tmpFile, $filename.'-thmb.jpg', $this->settings['THimageWidth'],
				$this->settings['THimageHeight'], $this->settings['imageColor']);
			unlink($tmpFile);

			$Eresus->db->updateItem($this->table['name'], $item, '`id` = "'.$item['id'].'"');
		}

		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ � ��
	 */
	public function update()
	{
		global $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".arg('update', 'int')."'");
		$image = $item['image'];
		$item['section'] = arg('section', 'int');
		if ( ! is_null(arg('section')) )
		{
			$item['active'] = arg('active', 'int');
		}
		// FIXME �� ������� position. ������� ���� ������ ����� ����������
		$item['posted'] = arg('posted', 'dbsafe');
		$item['block'] = arg('block', 'int');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['text'] = arg('text', 'dbsafe');
		$item['preview'] = arg('preview', 'dbsafe');
		if (empty($item['preview']) || arg('updatePreview'))
		{
			$item['preview'] = $this->createPreview($item['text']);
		}

		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$tmpFile = $Eresus->fdata . $this->name . '/uploaded.bin';
			upload('image', $tmpFile);

			$filename = $Eresus->fdata . 'articles/'.$image;
			if (($image != '') && (file_exists($filename.'.jpg')))
			{
				unlink($filename.'.jpg');
				unlink($filename.'-thmb.jpg');
			}
			$item['image'] = $item['id'].'_'.time();
			$filename = $Eresus->fdata . 'articles/'.$item['image'];
			useLib('glib');
			thumbnail($tmpFile, $filename.'.jpg', $this->settings['imageWidth'],
				$this->settings['imageHeight'], $this->settings['imageColor']);
			thumbnail($tmpFile, $filename.'-thmb.jpg', $this->settings['THimageWidth'],
				$this->settings['THimageHeight'], $this->settings['imageColor']);
			unlink($tmpFile);
		}
		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".arg('update', 'int')."'");

		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * �������� ������ �� ��
	 *
	 * @param int $id  ������������� ������
	 */
	public function delete($id)
	{
		global $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".arg('delete', 'int')."'");
		$filename = $Eresus->data . $this->name.'/'.$item['image'];
		if (file_exists($filename.'.jpg'))
		{
			unlink($filename.'.jpg');
			unlink($filename.'-thmb.jpg');
		}

		parent::delete($id);
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ �������� � ������
	 *
	 * @param string $template  ������
	 * @param array  $item      ������ �����
	 * @return string  HTML
	 */
	function replaceMacros($template, $item)
	{
		global $page;

		if (file_exists($GLOBALS['Eresus']->fdata . 'articles/'.$item['image'].'.jpg'))
		{
			$image = $GLOBALS['Eresus']->data . 'articles/'.$item['image'].'.jpg';
			$thumbnail = $GLOBALS['Eresus']->data . 'articles/'.$item['image'].'-thmb.jpg';
			$width = $this->settings['imageWidth'];
			$height = $this->settings['imageHeight'];
			$THwidth = $this->settings['THimageWidth'];
			$THheight = $this->settings['THimageHeight'];

		}
		else
		{
			$thumbnail = $image = $GLOBALS['Eresus']->style . 'dot.gif';
			$width = $height = $THwidth = $THheight = 1;
		}

		$result = str_replace(
			array(
				'$(caption)',
				'$(preview)',
				'$(text)',
				'$(posted)',
				'$(link)',
				'$(section)',
				'$(image)',
				'$(thumbnail)',
				'$(imageWidth)',
				'$(imageHeight)',
				'$(thumbnailWidth)',
				'$(thumbnailHeight)',
			),
			array(
				strip_tags(htmlspecialchars(StripSlashes($item['caption']))),
				StripSlashes($item['preview']),
				StripSlashes($item['text']),
				$item['posted'],
				$page->clientURL($item['section']).$item['id'].'/',
				$page->clientURL($item['section']),
				$image,
				$thumbnail,
				$width,
				$height,
				$THwidth,
				$THheight,
			),
			$template
		);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������������ ������ ��
	 *
	 * @return string|null
	 */
	function adminRenderContent()
	{
		if (!is_null(arg('action')) && arg('action') == 'textupdate')
		{
			$result = $this->text();
		}
		elseif (!is_null(arg('action')) && arg('action') == 'text')
		{
			$result = $this->adminRenderText();
		}
		else
		{
			$result = parent::adminRenderContent();
		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ���������� ������
	 *
	 * @return string
	 */
	public function adminAddItem()
	{
		global $page;

		$form = array(
			'name' => 'newArticles',
			'caption' => '�������� ������',
			'width' => '95%',
			'fields' => array (
				array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array ('type' => 'hidden', 'name' => 'section', 'value' => arg('section')),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'html', 'name' => 'text', 'label' => '������ �����', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => '������� ��������',
					'height' => '10'),
				array ('type' => ($this->settings['blockMode'] == self::BLOCK_MANUAL)?'checkbox':'hidden',
					'name' => 'block', 'label' => '���������� � �����'),
				array ('type' => 'file', 'name' => 'image', 'label' => '��������', 'width' => '100'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ��������� ������
	 *
	 * @return string
	 */
	public function adminEditItem()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".arg('id', 'int')."'");

		if (file_exists($Eresus->fdata . $this->name.'/'.$item['image'].'-thmb.jpg'))
		{
			$image = '�����������: <br /><img src="'. $Eresus->data . $this->name.'/'.$item['image'].
				'-thmb.jpg" alt="" />';
		}
		else
		{
			$image = '';
		}

		if (arg('action', 'word') == 'delimage')
		{
			$filename = $Eresus->fdata . $this->name.'/'.$item['image'];
			if (is_file($filename.'.jpg'))
			{
				unlink($filename.'.jpg');
			}
			if (is_file($filename.'-thmb.jpg'))
			{
				unlink($filename.'-thmb.jpg');
			}
			HTTP::redirect($page->url());
		}

		$form = array(
			'name' => 'editArticles',
			'caption' => '�������� ������',
			'width' => '95%',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'html', 'name' => 'text', 'label' => '������ �����', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => '������� ��������',
					'height' => '5'),
				array ('type' => 'checkbox', 'name'=>'updatePreview',
					'label'=>'�������� ������� �������� �������������', 'value' => false),
				array ('type' => ($this->settings['blockMode'] == self::BLOCK_MANUAL)?'checkbox':'hidden',
					'name' => 'block', 'label' => '���������� � �����'),
				array ('type' => 'file', 'name' => 'image', 'label' => '��������', 'width' => '100',
					'comment'=>(is_file($Eresus->fdata.$this->name.'/'.$item['image'].'.jpg') ?
						'<a href="'.$page->url(array('action'=>'delimage')).'">�������</a>' : '')),
				array ('type' => 'divider'),
				array ('type' => 'edit', 'name' => 'section', 'label' => '������', 'access'=>ADMIN),
				array ('type' => 'edit', 'name'=>'posted', 'label'=>'��������'),
				array ('type' => 'checkbox', 'name'=>'active', 'label'=>'�������'),
				array ('type' => 'text', 'value' => $image),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ��������
	 * @return string
	 */
	public function settings()
	{
		global $page;

		$form = array(
			'name' => 'settings',
			'caption' => $this->title.' '.$this->version,
			'width' => '500px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
				array('type'=>'text','value'=>
					'��� ������� ����� ������ ����������� ������ <b>$(ArticlesBlock)</b><br />'),
				array('type'=>'header','value'=>'��������� ��������������� ���������'),
				array('type'=>'memo','name'=>'tmplItem','label'=>'������ ��������������� ���������',
					'height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatFullText','label'=>'������ ����', 'width'=>'100px'),
				array('type'=>'header', 'value' => '��������� ������'),
				array('type'=>'edit','name'=>'itemsPerPage','label'=>'������ �� ��������','width'=>'50px',
					'maxlength'=>'2'),
				array('type'=>'memo','name'=>'tmplList','label'=>'������ ������','height'=>'3'),
				array('type'=>'text','value'=>'
					�������:<br />
					<strong>$(title)</strong> - ��������� ��������,<br />
					<strong>$(content)</strong> - ������� ��������,<br />
					<strong>$(items)</strong> - ������ ������
				'),
				array('type'=>'memo','name'=>'tmplListItem','label'=>'������ �������� ������',
					'height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatPreview','label'=>'������ ����', 'width'=>'100px'),
				array('type'=>'select','name'=>'listSortMode','label'=>'����������',
					'values' => array('posted', 'position'),
					'items' => array('�� ���� ����������', '������')),
				array('type'=>'checkbox','name'=>'listSortDesc','label'=>'� �������� �������'),
				array('type'=>'header', 'value' => '���� ������'),
				array('type'=>'select','name'=>'blockMode','label'=>'����� ����� ������',
					'values' => array(self::BLOCK_NONE, self::BLOCK_LAST, self::BLOCK_MANUAL),
					'items' => array('���������','��������� ������','������ ����� ������')),
				array('type'=>'memo','name'=>'tmplBlockItem','label'=>'������ �������� �����',
					'height'=>'3'),
				array('type'=>'edit','name'=>'blockCount','label'=>'����������', 'width'=>'50px'),
				array('type'=>'header', 'value' => '������� ��������'),
				array('type'=>'edit','name'=>'previewMaxSize','label'=>'����. ������ ��������',
					'width'=>'50px', 'maxlength'=>'4', 'comment'=>'��������'),
				array('type'=>'checkbox','name'=>'previewSmartSplit','label'=>'"�����" �������� ��������'),
				array('type'=>'header', 'value' => '��������'),
				array('type'=>'edit','name'=>'imageWidth','label'=>'������', 'width'=>'100px'),
				array('type'=>'edit','name'=>'imageHeight','label'=>'������', 'width'=>'100px'),
				array('type'=>'edit','name'=>'THimageWidth','label'=>'������ ���������', 'width'=>'100px'),
				array('type'=>'edit','name'=>'THimageHeight','label'=>'������ ���������', 'width'=>'100px'),
				array('type'=>'edit','name'=>'imageColor','label'=>'����� ����', 'width'=>'100px',
					'comment' => '#RRGGBB'),
				array('type'=>'divider'),
				array('type'=>'text', 'value'=>
					"��� �������� �������� ��������������� ���������, �������� ������ � �������� ����� " .
					"����� ������������ �������:<br />\n".
					"<b>$(caption)</b> - ���������<br />\n".
					"<b>$(preview)</b> - ������� �����<br />\n".
					"<b>$(text)</b> - ������ �����<br />\n".
					"<b>$(posted)</b> - ���� ����������<br />\n".
					"<b>$(link)</b> - ����� ������ (URL)<br />\n".
					"<b>$(section)</b> - ����� ������ ������ (URL)<br />\n".
					"<b>$(image)</b> - ����� �������� (URL)<br />\n".
					"<b>$(thumbnail)</b> - ����� ���������� (URL)<br />\n".
					"<b>$(imageWidth)</b> - ������ ��������<br />\n".
					"<b>$(imageHeight)</b> - ������ ��������<br />\n".
					"<b>$(thumbnailWidth)</b> - ������ ���������<br />\n".
					"<b>$(thumbnailHeight)</b> - ������ ���������<br />\n"
				),
		),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������������ ��������
	 *
	 * @return string
	 */
	public function clientRenderContent()
	{
		global $Eresus, $page;

		if ($page->topic)
		{
			$acceptUrl = $Eresus->request['path'] .
				($page->subpage !== 0 ? 'p' . $page->subpage . '/' : '') .
				($page->topic !== false ? $page->topic . '/' : '');
			if ($acceptUrl != $Eresus->request['url'])
			{
				$page->httpError(404);
			}
		}
		else
		{
			$acceptUrl = $Eresus->request['path'] .
				($page->subpage !== 0 ? 'p' . $page->subpage . '/' : '');
			if ($acceptUrl != $Eresus->request['url'])
			{
				$page->httpError(404);
			}
		}

		return parent::clientRenderContent();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ ������
	 *
	 * @param array $options  �������� ������ ������
	 *              $options['pages'] bool ���������� ������������� �������
	 *              $options['oldordering'] bool ����������� ��������
	 * @return string
	 */
	public function clientRenderList($options = null)
	{
		global $page;

		$item = array(
			'items' => parent::clientRenderList($options),
			'title' => $page->title,
			'content' => $page->content,
		);
		$result = parent::replaceMacros($this->settings['tmplList'], $item);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ � �����
	 *
	 * @param array $item  �������� ������
	 * @return string
	 */
	public function clientRenderListItem($item)
	{
		$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatPreview']);
		$result = $this->replaceMacros($this->settings['tmplListItem'], $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������
	 *
	 * @return string
	 */
	public function clientRenderItem()
	{
		global $Eresus, $page;

		if ($page->topic != (string) ((int) ($page->topic)))
		{
			$page->httpError(404);
		}

		$item = $Eresus->db->selectItem($this->table['name'],
			"(`id`='" . $page->topic . "') AND (`active`='1')");
		if (is_null($item))
		{
			$item = $page->httpError(404);
			$result = $item['content'];
		}
		else
		{
			$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatFullText']);
			$result = $this->replaceMacros($this->settings['tmplItem'], $item);
		}
		$page->section[] = $item['caption'];
		$item['access'] = $page->access;
		$item['name'] = $item['id'];
		$item['title'] = $item['caption'];
		$item['hint'] = $item['description'] = $item['keywords'] = '';
		$Eresus->plugins->clientOnURLSplit($item, arg('url'));
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������� clientOnPageRender
	 *
	 * @param string $text  HMTL ��������
	 * @return string
	 */
	public function clientOnPageRender($text)
	{
		$articles = $this->renderArticlesBlock();
		$text = str_replace('$(ArticlesBlock)', $articles, $text);
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������������ ������ �� ������������ ���������� ������
	 *
	 * @param int $id  ID ������
	 *
	 * @return void
	 *
	 * @uses DB::getHandler
	 * @uses DB::execute
	 * @uses HTTP::redirect
	 */
	public function toggle($id)
	{
		global $page;

		$q = DB::getHandler()->createUpdateQuery();
		$e = $q->expr;
		$q->update($this->table['name'])
			->set('active', $e->not('active'))
			->where($e->eq('id', $q->bindValue($id, null, PDO::PARAM_INT)));
		DB::execute($q);

		HTTP::redirect(str_replace('&amp;', '&', $page->url()));
	}
	//-----------------------------------------------------------------------------

	/**
	 * �������� �������� ������
	 *
	 * @param string $text
	 * @return string
	 */
	private function createPreview($text)
	{
		$text = trim(preg_replace('/<.+>/Us',' ',$text));
		$text = str_replace(array("\n", "\r"), ' ', $text);
		$text = preg_replace('/\s{2,}/', ' ', $text);

		if (!$this->settings['previewMaxSize'])
		{
			$this->settings['previewMaxSize'] = 500;
		}

		if ($this->settings['previewSmartSplit'])
		{
			preg_match("/\A(.{1,".$this->settings['previewMaxSize']."})(\.\s|\.|\Z)/s", $text, $result);
			$result = $result[1].'...';
		}
		else
		{
			$result = substr($text, 0, $this->settings['previewMaxSize']);
			if (strlen($text) > $this->settings['previewMaxSize'])
			{
				$result .= '...';
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ �� ��������
	 */
	private function text()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem('pages', '`id`="' . arg('section', 'int') . '"');
		$item['content'] = $Eresus->db->escape($Eresus->request['arg']['content']);
		$item = array('id' => $item['id'], 'content' => $item['content']);
		$Eresus->db->updateItem('pages', $item, '`id`="' . arg('section', 'int') . '"');

		HTTP::redirect(str_replace('&amp;', '&', $page->url(array('action' => 'text'))));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ �������������� ������ �� ��������
	 *
	 * @return string
	 */
	private function adminRenderText()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem('pages', '`id`="' . arg('section', 'int') . '"');
		$form = array(
			'name' => 'contentEditor',
			'caption' => '����� �� ��������',
			'width' => '95%',
			'fields' => array(
				array('type' => 'hidden', 'name' => 'action', 'value' => 'textupdate'),
				array('type' => 'html', 'name' => 'content', 'height' => '400px',
					'value' => $item['content']),
			),
			'buttons'=> array('ok', 'reset'),
		);

		$result = $page->renderForm($form);
		return $page->renderTabs($this->table['tabs']) . $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ����� ������
	 *
	 * @return string
	 */
	private function renderArticlesBlock()
	{
		global $Eresus;

		$result = '';
		$items = $Eresus->db->select($this->table['name'],
			"`active`='1'" . (
				$this->settings['blockMode'] == self::BLOCK_MANUAL ? " AND `block`='1'" : ''
			),
			($this->table['sortDesc'] ? '-' : '') . $this->table['sortMode'], '',
			$this->settings['blockCount']);

		if (count($items))
		{
			foreach ($items as $item)
			{
				$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatPreview']);
				$result .= $this->replaceMacros($this->settings['tmplBlockItem'], $item);
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
}
