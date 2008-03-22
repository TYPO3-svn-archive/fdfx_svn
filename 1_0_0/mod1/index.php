<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Peter Russ (peter.russ@4many.net)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Module 'SVN' for the 'fdfx_svn' extension.
 *
 * @author	Peter Russ <peter.russ@4many.net>
 */
// DEFAULT initialization of a module [BEGIN]
unset ($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:fdfx_svn/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]
class tx_fdfxsvn_module1 extends t3lib_SCbase
{
	var $pageinfo;
	var $extensionInfoArray;
	var $msg=array();
	var $error=false;
	var $extDir='uploads/tx_fdfxsvn/';
	/**
	 *
	 */
	function init()
	{
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		parent :: init();
	}
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()
	{
		global $LANG;
		$this->MOD_MENU = array (
			"function" => array (
				"1" => $LANG->getLL("function1"),
				"2" => $LANG->getLL("function2"),
				"3" => $LANG->getLL("function3"),
				'4'	=> $LANG->getLL('function4'),
		));
		if (!$this->conf['enableEmail'])
		{
			unset($this->MOD_MENU['function']['2']);
		}
		parent :: menuConfig();
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);
		$modTSconfig=t3lib_BEfunc::getModTSconfig($this->id,'mod.'.'web_list');
		$this->modTSconfig['properties']['alternateBgColors']=$modTSconfig['properties']['alternateBgColors'];
	}
	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	* Main function of the module. Write the content to $this->content
	*/
	function main()
	{
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc :: readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		$this->conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['fdfx_svn']);
		//		$this->conf = $conf['fdfx_svn.'];
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))
		{
			// Draw the header.
			$this->doc = t3lib_div :: makeInstance("bigDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="POST">';
			// JavaScript
			$this->doc->JScode = '
<script language="javascript" type="text/javascript">
	script_ended = 0;
	function jumpToUrl(URL)	{
		document.location = URL;
	}
</script>
																								';
			$this->doc->postCode = '
<script language="javascript" type="text/javascript">
	script_ended = 1;
	if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
</script>
																								';
			$headerSection = $this->doc->getHeader("pages", $this->pageinfo, $this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div :: fixed_lgd_pre($this->pageinfo["_thePath"], 50);
			$this->content .= $this->doc->startPage($LANG->getLL("title"));
			$this->content .= $this->doc->header($LANG->getLL("title"));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section("", $this->doc->funcMenu($headerSection, t3lib_BEfunc :: getFuncMenu($this->id, "SET[function]", $this->MOD_SETTINGS["function"], $this->MOD_MENU["function"])));
			$this->content .= $this->doc->divider(5);
			// Render content:
			$this->moduleContent();
			// ShortCut
			if ($BE_USER->mayMakeShortcut())
			{
				$this->content .= $this->doc->spacer(20).$this->doc->section("", $this->doc->makeShortcutIcon("id", implode(",", array_keys($this->MOD_MENU)), $this->MCONF["name"]));
			}
			$this->content .= $this->doc->spacer(10);
		}
		else
		{
			// If no access or if ID == zero
			$this->doc = t3lib_div :: makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->content .= $this->doc->startPage($LANG->getLL("title"));
			$this->content .= $this->doc->header($LANG->getLL("title"));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}
	/**
	 * Prints out the module HTML
	 */
	function printContent()
	{
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}
	/**
	 * Generates the module content
	 */
	function moduleContent()
	{
		global $LANG, $BE_USER, $EM_CONF;
		switch ((string) $this->MOD_SETTINGS["function"])
		{
			case '1' :
				$gpVar = t3lib_div :: _GP('f1_submit');
				$content .= $LANG->getLL('f1_comment').'<hr>';
				if ($gpVar)
				{
					if ($this->conf['code'])
					{
						$content .= system(escapeshellcmd($this->conf['code']));
					}
					else
					{
						$content .= $LANG->getLL('f1_error_no_code').'<hr>';
					}
				}
				$content .= '<form action="" method="post"><input name="f1_submit" type="submit" value="'.$LANG->getLL("f1_submit").'"></form>';
				$this->content .= $this->doc->section($LANG->getLL('f1_header'), $content, 0, 1);
				break;
			case '2' :
				$gpVar = t3lib_div :: _GP('f2_submit');
				$content .= $LANG->getLL('f2_comment').'<hr>';
				if ($gpVar)
				{
					$recipient = t3lib_div :: _GP('f2_recipient');
					if ($recipient)
					{
						$msg = t3lib_div :: _GP('f2_msg');
						if (!isset ($msg) || $msg == '')
						{
							$msg = $this->conf['defaultMessage'];
						}
						mail($recipient, $this->conf['defaultSubject'], $msg, 'from:'.$BE_USER->user['realName'].'<'.$BE_USER->user['email'].'>');
					}
					else
					{
						$content .= $LANG->getLL('f2_error_no_recipients').'<hr>';
					}
				}
				else
				{
					if ($BE_USER->user['realName'] == '' || $BE_USER->user['email'] == '')
					{
						$content .= $LANG->getLL('f2_error_usersetup_not_valid').'<pre>'.var_export($BE_USER->user, true).'</pre><hr>';
					}
					else
					{
						if ($this->conf['enableEmail'])
						{
							$content .= '<form action="" method="post">'.$LANG->getLL('f2_recipients').'<br>'.'<input type="text" size="50" name="f2_recipient" value="'.$this->conf['recipients'].'"><br>'.$LANG->getLL('f2_message').'<br>'.'<textarea cols="50" rows="5" name="f2_msg">'.$this->conf['defaultMessage'].'</textarea><br>'.'<br><input name="f2_submit" type="submit" value="'.$LANG->getLL("f2_submit").'"></form>';
						}
					}
				}
				$this->content .= $this->doc->section($LANG->getLL('f2_header'), $content, 0, 1);
				break;
			case '3' :
			case '4' :
				$type=($this->MOD_SETTINGS["function"]=='3')?'local':'global';
				$this->_loadVersions();
				$this->_addVersion($type);
				if (isset ($_POST['FDFXbutton']))
				{
					switch ($_POST['FDFXbutton'])
					{
						case 'save' :
							$this->_saveVersions();
							break;
						case 'download' :
							if ($_POST['FDFXVersion'])
							{
								$this->_download($_POST['FDFXVersion'],$type);
								exit();
							}
							break;
						case 'deploy':
							if ($_POST['FDFXVersion'] && $this->conf['enableDeploy'])
							{
								$error=$this->_deploy($_POST['FDFXVersion'],$type);
								if ($error)
								{
									$content .='<b>Errors while trying to deloy:</b><br/>'.(join('<br//>',$this->getLogMsg())).'<hr/>';
								} else {
									$msg=$this->getLogMsg();
									$this->resetLogMsg();
									$content .=$msg[0].'<br/>';
									if ($_POST['FDFXTrigger'])
									{
										$success=false;
										$trigger=null;
										$buffer='';
										if ($type=='local' && $this->conf['ftpLocalTrigger'])
										{
											$trigger=$this->conf['ftpLocalTrigger'];
										} elseif ($type=='global' && $this->conf['ftpGlobalTrigger']){
											$trigger=$this->conf['ftpGlobalTrigger'];
										}
										if ($trigger)
										{
											if ($fp=@fopen($trigger,'r'))
											{
												while (!feof($fp))
												{
													$buffer .= fgets($fp,10240);
												}
												$buffer=html_entity_decode(strip_tags(trim($buffer)));
												$success=true;
											}
										}
										if ($success)
										{
											$content .= '<b>Trigger pulled</b> for "'.$type.'" on remote server.<br />';
											if (strlen($buffer)>0)
											{
												$content .= '<b>Answer was</b>:<br><form><textarea cols="60" rows="5">'.$buffer.'</textarea></form>';
											}
										} else {
											$content .= '<b>Can not execute the trigger</b> command for "'.$type.'" on remote server.<br/><b>Reason</b>:<br/>'.print_r(error_get_last());
										}
									}
									unset($_POST['FDFXVersion']);
								}
							} else {
								#TODO: error Handling here
								# either no parameter or deploy was disabled meanwhile
							}
							break;

					}
				}
				$this->_extensions($type);
				$content .= $this->wrapExtList($type);
				$this->content .= $this->doc->section($LANG->getLL('f'.$this->MOD_SETTINGS["function"].'_header'), $content, 0, 1);
				break;
		}
	}
	function _deploy($version,$type='local')
	{
		$this->resetLogMsg();
		$ftpId=null;
		if ($this->parameterOk())
		{
			$file=$this->_download($version,$type,false);
			if ($file===false)
			{
				$this->addLogMsg(5001,'No files found for deployment');
			} else {
				if (file_exists($file))
				{
					$ftpId=@ftp_connect($this->conf['ftpServer']);
					if ($ftpId)
					{
						if (@ftp_login($ftpId,$this->conf['ftpUser'],$this->conf['ftpPassword']))
						{
							$dir=($type=='local')?$this->conf['ftpPathLocal']:$this->conf['ftpPathGlobal'];
							if (@ftp_chdir($ftpId,$dir))
							{
								if ($fp=@fopen($file,'r'))
								{
									if (@ftp_fput($ftpId,basename($file),$fp,FTP_BINARY))
									{
										$this->addLogMsg(5555,'File '.basename($file).' deployed to server "'.$this->conf['ftpServer'].'".',false);

									} else {
										$this->addLogMsg(5015,'Error while trying to upload file "'.basename($file).'" to server "'.$this->conf['ftpServer'].'"!');
									}

								} else {
									$this->addLogMsg(5014,'Can not open file "'.$file.'" to transfer to server "'.$$this->conf['ftpServer'].'"!');
								}
							} else {
								$this->addLogMsg(5013,'Can not chdir to "'.$dir.'" on ftp server "'.$this->conf['ftpServer'].'!');
							}
						} else {
							$this->addLogMsg(5012,'Login to ftp server '.$this->conf['ftpServer'].' for user '.$this->conf['ftpUser'].' failed.');
						}
					} else {
						$this->addLogMsg(5011,'Can not connect to ftp server "'.$this->conf['ftpServer'].'"!');
					}
					@unlink($file);
				} else {
					$this->addLogMsg(5002,'File '.$file.' can\'t be accessed. Check what happend.');
				}
			}
		} else {
			$this->error=true;
		}
		if ($ftpId)
		{
			ftp_close($ftpId);
		}
		return $this->error;
	}
	function parameterOk()
	{
		$ok=true;
		if (!$this->conf['ftpServer'])
		{
			$this->addLogMsg(5901,'FTP server not set!');
			$ok=false;
		}
		if (!$this->conf['ftpUser'])
		{
			$this->addLogMsg(5902,'FTP user not set!');
			$ok=false;
		}
		if (!$this->conf['ftpPassword'])
		{
			$this->addLogMsg(5903,'FTP password not set!');
			$ok=false;
		}
		return $ok;
	}
	function resetLogMsg()
	{
		$this->error=false;
		$this->msg=array();
	}
	function addLogMsg($no,$msg,$isError=true)
	{
		$this->error= $this->error || $isError;
		$this->msg[]=array($no,$msg);
	}
	function getLogMsg($reverse=true)
	{
		$result=$reverse? array_reverse($this->msg):$this->msg;
		$messages=array();
		foreach ($result as $msg)
		{
			$messages[]=$msg[1];
		}
		return $messages;
	}
	function _download($version,$type='local',$download=true)
	{
		$f1_temp = date("Y-m-d", time()).'-'.substr(MD5(microtime()), 0, 20);
		$tempPath = PATH_site.$this->conf['backup_path'];
		if (TYPO3_OS == 'WIN')
		{
			chdir(PATH_typo3conf);
			$this->zip_path = $this->conf['7zip_path'];
			$this->backup_path = '..\\..\\'.$this->conf['backup_path'];
			$createCommand = $this->zip_path.' a -ttar '.$this->backup_path.$f1_temp.'.tar ';
			foreach ($this->versions[$type][$version] as $key => $value)
			{
				$createCommand .= 'ext/'.$key.'/* -r ';
			}
			exec($createCommand);
			$createCommand = $this->zip_path.' a -tgzip '.$tempPath.$f1_temp.'.tar.gz '.$tempPath.$f1_temp.'.tar';
			exec($createCommand);
			unlink($tempPath.$f1_temp.'.tar');
		}
		else
		{ // UNIX/LINUX
			//make exclude clause
			$this->tar_path = $this->conf['tar_path'];
			$this->excluded = $this->conf['excluded'];
			if ($this->excluded != '')
			{
				$typeArray = explode(',', $this->excluded);
				$excludeClause = '';
				foreach ($typeArray as $typ)
				{
					$excludeClause .= ' --exclude="'.$typ.'" ';
				}
			}
			$createCommand = $this->tar_path.' cz -f '.$tempPath.$f1_temp.'.tar.gz -C '.(($type=='local')?PATH_typo3conf:PATH_typo3);
			foreach ($this->versions[$type][$version] as $key => $value)
			{
				$createCommand .= ' ext/'.$key;
			}
			exec($createCommand.$excludeClause);
		}
		$file = $tempPath.$f1_temp.'.tar.gz';
		if ($download)
		{
			$fp = fopen($file, 'r');
			if ($fp)
			{
				$len = filesize($file);
				$pdfFile = $version.'.tar.gz';
				if (isset ($_SERVER['HTTP_USER_AGENT']))
				{
					header("Content-type: application/x-compressed-tar");
					if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
					{
						header("Cache-Control: ");
						header("Pragma: ");
						header("Content-Disposition: inline; filename=\"$pdfFile\"");
					}
					else
					{
						header('Cache-Control: no-cache, must-revalidate');
						header('Pragma: no-cache');
						header("Content-Disposition: inline; filename=\"$pdfFile\"");
					}
					header("Content-length: ".$len);
				}
				fpassthru($fp);
				@unlink($file);
			}
		} else {
			if (file_exists($file))
			{
				return ($file);
			} else {
				return false;
			}
		}
	}
	function wrapExtList($type='local')
	{
		$FDFXVersion = (t3lib_div :: _GP('FDFXVersion')) ? t3lib_div :: _GP('FDFXVersion') : '';
		$create = ($FDFXVersion) ? "&nbsp;<input type=\"submit\" name=\"FDFXbutton\" onclick=\"document.forms['fdfx'].target='_blank';submit();\" title=\"download to local server\" value=\"download\"/>" : '';
		if ($this->conf['enableDeploy'] && $FDFXVersion)
		{
			if (($type=='local' && $this->conf['ftpLocalTrigger']) || ($type=='global' && $this->conf['ftpGlobalTrigger']))
			{
				$create .= "&nbsp;<input type=\"checkbox\" name=\"FDFXTrigger\" title=\"Activate this clickbox to get the files expanded on remote server\"/>";
			}
			$create .= "&nbsp;<input type=\"submit\" name=\"FDFXbutton\" title=\"deploy to remote server in the wild\" value=\"deploy\"/>";
		}
		$selector = $this->getSelector($FDFXVersion,$type);
		$content = "</form><form name=\"fdfx\" target=\"_self\" method=\"post\"><div style=\"text-align:right;\"><input type=\"button\" value=\"clear\" onclick=\"document.getElementById('FDFXVersion').value='';submit();\"<input type=\"reset\" value=\"reset\"/>".$selector."<input id=\"FDFXVersion\" name=\"FDFXVersion\" size=\"32\" maxlength=\"255\" value=\"".$FDFXVersion."\"/><input type=\"submit\" name=\"FDFXbutton\" value=\"save\"/>".$create."</div>";
		$content .= '<table colspacing="0" colpadding="2" border="0" class="typo3-dblist" width="100%"><tr><td class="c-headLine">Icon</td><td class="c-headLine">Key</td><td class="c-headLine">Description</td><td class="c-headLine">Version</td><td class="c-headLine">Dependencies</td><td class="c-headLine">Add</td></tr>';
		$path=($type=='local')?'../../../../typo3conf/ext/':'../../../../typo3/ext/';
		$alternateBgColors=$this->modTSconfig['properties']['alternateBgColors'];
		$cc=1;
		foreach ($this->extensionInfoArray[$type] as $i => $values)
		{
			$title = $values['extKey'];
			$row_bgColor=$alternateBgColors?(($cc%2)?'' :' class="db_list_alt"') :	'';
			$title .= ($values['eInfo']['dependencies']) ? ' requires '.chr(10).$values['eInfo']['dependencies'] : '';
			$checked = ($this->versions[$type][$FDFXVersion][$values['extKey']]) ? ' checked="checked"' : '';
			$content .= '<tr><td'.$row_bgColor.'><img src="'.$path.$values['extKey'].'/ext_icon.gif"/></td><td'.$row_bgColor.'>'.$values['extKey'].'</td><td nowrap="nowrap" '.$row_bgColor.'>'.$values['eInfo']['title'].'</td><td'.$row_bgColor.'>'.$values['eInfo']['version'].'</td><td'.$row_bgColor.'>'.join(', ',explode(',',$values['eInfo']['dependencies'])).'</td><td'.$row_bgColor.'><input type="checkbox" name="'.$values['extKey'].'" title="'.$title.'"'.$checked.'/></td></tr>';
			$cc++;
		}
		$content .= '</table></form>';
		return $content;
	}
	function getSelector($version,$type='local')
	{
		$selector = '';
		if (isset ($this->versions[$type]) && is_array($this->versions))
		{
			$selector = "<select name=\"FDFXSelector\" size=\"1\" onchange=\"document.getElementById('FDFXVersion').value=this.value;this.form.submit()\"><option></option>";
			$array=array_reverse($this->versions[$type],true);
			foreach ($array as $key => $value)
			{
				$sel = ($key == $version) ? ' selected="selected"' : '';
				$selector .= '<option'.$sel.'>'.$key.'</value>';
			}
			$selector .= "</select>";
		}
		return $selector;
	}
	function _addVersion($type='local')
	{
		$exList = array ('SET', 'FDFXbutton', 'FDFXVersion', 'FDFXSelector');
		$arr = array ();
		foreach ($_POST as $key => $value)
		{
			if (!in_array($key, $exList))
			{
				$arr[$key] = $value;
			}
		}
		if (count($arr) > 0)
		{
			$this->_setVersion($_POST['FDFXVersion'], $arr,$type);
		}
	}
	function _setVersion($version, $arr,$type='local')
	{
		if (!isset ($this->versions) || !is_array($this->versions))
		{
			$this->versions = array ();
		}
		$this->versions[$type][$version] = $arr;
		$this->_saveVersions();
	}
	function _loadVersions()
	{
		$this->versions = false;
		if (@ is_dir(PATH_site.$this->extDir))
		{

			$file = t3lib_div::getFileAbsFileName($this->extDir.'fdfx_svn-versions.'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'].'.ser');
			if (@ is_file($file))
			{
				$this->versions = unserialize(t3lib_div::getURL($file));
			}
		}
	}
	function _saveVersions()
	{
		if ($this->versions)
		{
			if (@ is_dir(PATH_site.$this->extDir))
			{
				unset ($this->versions['']);
				$file = t3lib_div::getFileAbsFileName($this->extDir.'fdfx_svn-versions.'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'].'.ser');
				t3lib_div::writeFile($file,serialize($this->versions));
			}
		}
	}
	function _extensions($type='local')
	{
		$path=($type=='local')?PATH_typo3conf:PATH_typo3;
		$extDir = $path.'ext/';
		if (@ is_dir($extDir))
		{
			$this->extensionInfoArray = array ();
			// Get extensions in directory
			$extensions = t3lib_div :: get_dirs($extDir);
			if (is_array($extensions))
			{
				$this->extensionInfoArray = array ();
				foreach ($extensions as $extKey)
				{
					// Getting and setting information for extension:
					if ($this->isExtension($extDir, $extKey))
					{
						$this->extensionInfoArray[$type][] = array ('extKey' => $extKey, 'eInfo' => $this->getExtensionInfo($extDir, $extKey));
					}
				}
				if (is_array($this->extensionInfoArray[$type]))
				{
					sort($this->extensionInfoArray[$type]);
				}
			}
		}
	}
	function isExtension($path, $extKey)
	{
		$file = $path.$extKey.'/ext_emconf.php';
		return (@ is_file($file)) ? true : false;
	}
	function getExtensionInfo($path, $extKey)
	{
		$file = $path.$extKey.'/ext_emconf.php';
		if (@ is_file($file))
		{
			$_EXTKEY = $extKey;
			include ($file);
			$eInfo = array ();
			// Info from emconf:
			$eInfo = $EM_CONF[$extKey];
			return $eInfo;
		}
		return false;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_svn/mod1/index.php'])
{
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_svn/mod1/index.php']);
}
// Make instance:
$SOBE = t3lib_div :: makeInstance('tx_fdfxsvn_module1');
$SOBE->init();
/*
 // Include files?
 foreach ($SOBE as $INC_FILE)
 include_once ($INC_FILE);
 */
$SOBE->main();
$SOBE->printContent();
?>