<?php
namespace Fixpunkt\Backendtools\Controller;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Kurt Gusbeth <k.gusbeth@fixpunkt.com>, fixpunkt werbeagentur gmbh
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * SessionController
 */
class SessionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

	/**
	 * contentRepository
	 *
	 * @var \Fixpunkt\Backendtools\Domain\Repository\SessionRepository
	 */
	protected $sessionRepository;

	/**
	 * Injects the session-Repository
	 *
	 * @param \Fixpunkt\Backendtools\Domain\Repository\SessionRepository $sessionRepository
	 */
	public function injectSessionRepository(\Fixpunkt\Backendtools\Domain\Repository\SessionRepository $sessionRepository) {
		$this->sessionRepository = $sessionRepository;
	}
	
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
    	$beuser_id = $GLOBALS['BE_USER']->user['uid']; 
    	//$pageRep = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
    	$domains = $this->sessionRepository->getDomains();
    	$result = $this->sessionRepository->findByAction('list', $beuser_id);
 		if ($result->count() == 0) {
 			$new = TRUE;
 			$default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
 			$default->setAction('list');
 			$default->setValue1(0);
 			$default->setValue2(0);
 			$default->setValue3(0);
 			$default->setValue4('');
 			$default->setValue5('');
 			$default->setValue6('');
 		} else {
 			$new = FALSE;
 			$default = $result[0];
 		}
 			
    	if ($this->request->hasArgument('my_c')) {
    		$my_c = intval($this->request->getArgument('my_c'));		// content visibility
    		$default->setValue1($my_c);
    	} else $my_c = $default->getValue1();
    	if ($this->request->hasArgument('my_p')) {
    		$my_p = intval($this->request->getArgument('my_p'));		// pages visibility
    		$default->setValue2($my_p);
    	} else $my_p = $default->getValue2();
    	if ($this->request->hasArgument('my_type')) {
    		$my_type = intval($this->request->getArgument('my_type'));	// type
    		$default->setValue3($my_type);
    	} else $my_type = $default->getValue3();
   		if ($this->request->hasArgument('my_value')) {
   			$my_value = $this->request->getArgument('my_value');		// type value
   			$default->setValue4($my_value);
   		} else $my_value = $default->getValue4();
   		if ($this->request->hasArgument('my_flexform')) {
   			$my_flexform = $this->request->getArgument('my_flexform');	// flexform value
   			$default->setValue5($my_flexform);
    	} else $my_flexform = $default->getValue5();
    	if ($this->request->hasArgument('my_exclude')) {
    		$my_exclude = $this->request->getArgument('my_exclude');	// exclude type
    		$default->setValue6($my_exclude);
    	} else $my_exclude = $default->getValue6();
    	if ($this->request->hasArgument('my_page')) {
    		$my_page = intval($this->request->getArgument('my_page'));		// elements per page
    		$default->setPageel($my_page);
    	} else $my_page = $default->getPageel();
    	if (!$my_page) {
    		$my_page = $this->settings['pagebrowser']['itemsPerPage'];
	    	if (!$my_page) {
	    		$my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
	    	}
    	} else {
    		$this->settings['pagebrowser']['itemsPerPage'] = $my_page;
    	}
    	if ($this->request->hasArgument('my_outp')) {
    		$my_outp = intval($this->request->getArgument('my_outp'));		// output
    	} else $my_outp = 0;
    	
    	if ($new) {
    		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    		$backendUserRepository = $objectManager->get(BackendUserRepository::class);
    		/** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
    		$user = $backendUserRepository->findByUid($beuser_id);
    		$default->setBeuser($user);
    		$this->sessionRepository->add($default);
    		$persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
    		$persistenceManager->persistAll();
    	} else { 
    		$this->sessionRepository->update($default);
    	}
    	
    	$pages = $this->sessionRepository->getPagesWithExtensions($my_c, $my_p, $my_type, $my_value, $my_flexform, $my_exclude);
    	
    	// Assign
    	$this->view->assign('my_p', $my_p);
    	$this->view->assign('my_c', $my_c);
    	$this->view->assign('my_type', $my_type);
    	$this->view->assign('my_value', $my_value);
    	$this->view->assign('my_exclude', $my_exclude);
    	$this->view->assign('my_flexform', $my_flexform);
    	$this->view->assign('my_page', $my_page);
    	$this->view->assign('my_outp', $my_outp);
    	$this->view->assign('rows', count($pages));
    	$this->view->assign('pages', $pages);
    	$this->view->assign('domains', $domains);
    	$this->view->assign('settings', $this->settings);
    }
    
    /**
     * action filedeletion
     *
     * @return void
     */
    public function filedeletionAction()
    {
    	$beuser_id = $GLOBALS['BE_USER']->user['uid'];
    	$result = $this->sessionRepository->findByAction('filedeletion', $beuser_id);
    	if ($result->count() == 0) {
    		$new = TRUE;
    		$default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
    		$default->setAction('filedeletion');
    		$default->setValue1(0);
    		$default->setValue2(0);
    		$default->setValue3(0);
    		$default->setValue4('');
    		$default->setValue5('0');
    	} else {
    		$new = FALSE;
    		$default = $result[0];
    	}
    	
    	if ($this->request->hasArgument('method')) {
    		$method = intval($this->request->getArgument('method'));
    		$default->setValue1($method);
    	} else $method = $default->getValue1();
    	if ($this->request->hasArgument('bytes')) {
    		$bytes = intval($this->request->getArgument('bytes'));
    		$default->setValue2($bytes);
    	} else $bytes = $default->getValue2();
    	if ($this->request->hasArgument('convert')) {
    		$convert = $this->request->getArgument('convert');
    		$default->setValue5($convert);
    	} else $convert = $default->getValue5();
    	if ($this->request->hasArgument('delfile')) {
    		$delfile = $this->request->getArgument('delfile');
    	//	$default->setValue4($delfile);
    	} else $delfile = ''; // $default->getValue4();
    	

    	if ($new) {
    		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    		$backendUserRepository = $objectManager->get(BackendUserRepository::class);
    		/** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
    		$user = $backendUserRepository->findByUid($beuser_id);
    		$default->setBeuser($user);
    		$this->sessionRepository->add($default);
    		$persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
    		$persistenceManager->persistAll();
    	} else {
    		$this->sessionRepository->update($default);
    	}
    	
    	$groesse = 0;
    	$groesse_total = 0;
    	$content = '';
    	
    	if ($delfile) {
    		$total=0;
    		$success=0;
    		$filename = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . 'fileadmin/' . $delfile;
    		if (is_file($filename) && file_exists($filename)) {
    			if (!$method) $content .= "This is the file content:<br />\n";
    			$filecontent = fopen($filename,"r");
    			while (!feof($filecontent)) {
    				$row = trim(fgets($filecontent));
    				if ($convert == 'iso') $row = utf8_decode ( $row );
    				if ($convert == 'utf8') $row = utf8_encode ( $row );
    				if (is_file($row) && file_exists($row)) {
	    				if ($bytes) {
	    					$groesse = filesize($row);
	    					$groesse_total += $groesse;
	    				}
	    				if ($method && strpos($row, '/uploads/')>0) {
	    					if (unlink($row)) $success++;
	    					else $content .= "$row could not be deleted!<br />\n";
	    				} else {
	    					$content .= ($bytes) ? "$row ($groesse bytes)<br />\n" : "$row<br />\n";
	    				}
    				} else {
    					$content .= "$row not found!<br />\n";
    				}
    				$total++;
    			}
    			fclose ($filecontent);
    			if ($bytes) $content .= "<br />That are $groesse_total bytes (".$this->formatBytes($groesse_total).").";
    			$content .= "<br />$success/$total files deleted.";
    		} else {
    			$content .= 'Note: file not found!!!';
    		}
    	}
    	$this->view->assign('method', $method);
    	$this->view->assign('bytes', $bytes);
    	$this->view->assign('convert', $convert);
    	$this->view->assign('delfile', $delfile);
    	$this->view->assign('message', $content);
    }

    /**
     * action images: images without alt- or title-tag
     *
     * @return void
     */
    public function imagesAction()
    {
    	$beuser_id = $GLOBALS['BE_USER']->user['uid']; 
    	$result = $this->sessionRepository->findByAction('images', $beuser_id);
    	if ($result->count() == 0) {
    		$new = TRUE;
    		$default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
    		$default->setAction('images');
    		$default->setValue1(0);
    	} else {
    		$new = FALSE;
    		$default = $result[0];
    	}
    	
    	if ($this->request->hasArgument('img_without')) {
    		$img_without = intval($this->request->getArgument('img_without'));
	    	$default->setValue1($img_without);
    	} else $img_without = $default->getValue1();
    	if ($this->request->hasArgument('my_page')) {
    		$my_page = intval($this->request->getArgument('my_page'));		// elements per page
    		$default->setPageel($my_page);
    	} else $my_page = $default->getPageel();
    	if (!$my_page) {
    		$my_page = $this->settings['pagebrowser']['itemsPerPage'];
    		if (!$my_page) {
    			$my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
    		}
    	} else {
    		$this->settings['pagebrowser']['itemsPerPage'] = $my_page;
    	}
    	
    	if ($img_without) {
    		$finalArray = $this->sessionRepository->getImagesWithout($img_without);
    	} else {
    		$finalArray = [];
    	}
    	
   		if ($new) {
   			$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
   			$backendUserRepository = $objectManager->get(BackendUserRepository::class);
   			/** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
   			$user = $backendUserRepository->findByUid($beuser_id);
   			$default->setBeuser($user);
   			$this->sessionRepository->add($default);
   			$persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
   			$persistenceManager->persistAll();
    		/*$def_uid = $default->getUid();
    		// leider komme ich nicht an das beuser-obj dran, also muss die beuser-uid per update hinzugefügt werden..
    		$update = array('beuser' => $beuser_id);
    		$success = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_backendtools_domain_model_session', 'uid='.$def_uid, $update);*/
   		} else
			$this->sessionRepository->update($default);
    			
    	$count=0;
    	if (($img_without == 1) && $this->request->hasArgument('replace_empty_alt')) {
    		// alt-Tags setzen. In der sys_file_metadata
    		foreach ($finalArray as $key => $imgArray) {
    			$uid = $imgArray['uid'];
    			if ($imgArray['ref_title'])
    				$finalArray[$key]['meta_alt'] = $imgArray['ref_title'];
    			else if ($imgArray['meta_title'])
    				$finalArray[$key]['meta_alt'] = $imgArray['meta_title'];
    			else {
    				if (strrpos($imgArray['name'], '.') > 0)
    					$finalArray[$key]['meta_alt'] = trim(str_replace('_', ' ', substr($imgArray['name'], 0, strrpos($imgArray['name'], '.'))));
    				else
    					$finalArray[$key]['meta_alt'] = trim(str_replace('_', ' ', $imgArray['name']));
    			}
    			//$update = array('alternative' => $finalArray[$key]['meta_alt']);
    			//$success = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_metadata', 'file='.$uid, $update);
    			$success = $this->sessionRepository->setAltOrTitle($uid, $finalArray[$key]['meta_alt'], '');
    			if ($success) $count++;
    		}
    	} else if (($img_without == 2) && $this->request->hasArgument('replace_empty_meta')) {
    		// title-Tags setzen. In der sys_file_metadata
    		foreach ($finalArray as $key => $imgArray) {
    			$uid = $imgArray['uid'];
    			if ($imgArray['ref_alt'])
    				$finalArray[$key]['meta_title'] = $imgArray['ref_alt'];
    			else if ($imgArray['meta_alt'])
    				$finalArray[$key]['meta_title'] = $imgArray['meta_alt'];
    			else {
    				if (strrpos($imgArray['name'], '.') > 0)
    					$finalArray[$key]['meta_title'] = trim(str_replace('_', ' ', substr($imgArray['name'], 0, strrpos($imgArray['name'], '.'))));
    				else
    					$finalArray[$key]['meta_title'] = trim(str_replace('_', ' ', $imgArray['name']));
    			}
    			//$update = array('title' => $finalArray[$key]['meta_title']);
    			//$success = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_metadata', 'file='.$uid, $update);
    			$success = $this->sessionRepository->setAltOrTitle($uid, '', $finalArray[$key]['meta_title']);
    			if ($success) $count++;
    		}
    	}

    	$this->view->assign('img_without', $img_without);
    	$this->view->assign('count', $count);
    	$this->view->assign('images', $finalArray);
    	$this->view->assign('my_page', $my_page);
    	$this->view->assign('settings', $this->settings);
    }
    
    /**
     * action unzip
     *
     * @return void
     */
    public function unzipAction()
    {
    	if ($this->request->hasArgument('zipfile'))
    		$zipfile = $this->request->getArgument('zipfile');		// zipfile
    	else $zipfile = '';
    	    	 
    	if ($this->request->hasArgument('zipfile')) {
    		$filename = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . 'fileadmin/' . $zipfile;
    		if (is_file($filename) && file_exists($filename)) {
    			$pathinfo = pathinfo($filename); 
    			$content = $this->unzip($filename, $pathinfo['dirname'] . '/');
    		} else {
    			$content = 'Note: file not found!!!';
    		}
    	} else {
    		$content = 'Note: the zip-file will be extracted in the folder where it lies.';
    	}
    	
    	$this->view->assign('zipfile', $zipfile);
    	$this->view->assign('message', $content);
    }
    
    /**
     * action pagesearch
     *
     * @return void
     */
    public function pagesearchAction()
    {
    	$beuser_id = $GLOBALS['BE_USER']->user['uid']; 
    	$result = $this->sessionRepository->findByAction('pagesearch', $beuser_id);
    	if ($result->count() == 0) {
    		$new = TRUE;
    		$default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
    		$default->setAction('pagesearch');
    		$default->setValue1(0);
    	} else {
    		$new = FALSE;
    		$default = $result[0];
    	}
    	
    	if ($this->request->hasArgument('my_c')) {
    		$my_c = intval($this->request->getArgument('my_c'));
    		$default->setValue1($my_c);
    	} else $my_c = $default->getValue1();
    	if ($this->request->hasArgument('my_p')) {
    		$my_p = intval($this->request->getArgument('my_p'));
    		$default->setValue2($my_p);
    	} else $my_p = $default->getValue2();
    	if ($this->request->hasArgument('exttoo')) {
    		$exttoo = intval($this->request->getArgument('exttoo'));
    		$default->setValue3($exttoo);
    	} else $exttoo = $default->getValue3();
    	if ($this->request->hasArgument('linksto')) {
    		$linksto = $this->request->getArgument('linksto');
    		$default->setValue4($linksto);
    	} else $linksto = $default->getValue4();
    	$linkto_uid = intval($linksto);
    	if ($this->request->hasArgument('my_page')) {
    		$my_page = intval($this->request->getArgument('my_page'));		// elements per page
    		$default->setPageel($my_page);
    	} else $my_page = $default->getPageel();
    	if (!$my_page) {
    		$my_page = $this->settings['pagebrowser']['itemsPerPage'];
    		if (!$my_page) {
    			$my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
    		}
    	} else {
    		$this->settings['pagebrowser']['itemsPerPage'] = $my_page;
    	}
    	
    	if ($new) {
    		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    		$backendUserRepository = $objectManager->get(BackendUserRepository::class);
    		/** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
    		$user = $backendUserRepository->findByUid($beuser_id);
    		$default->setBeuser($user);
    		$this->sessionRepository->add($default);
    		$persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
    		$persistenceManager->persistAll();
    		/*$def_uid = $default->getUid();
    		// leider komme ich nicht an das beuser-obj dran, also muss die beuser-uid per update hinzugefügt werden..
    		$update = array('beuser' => $beuser_id);
    		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_backendtools_domain_model_session', 'uid='.$def_uid, $update);*/
    	} else
    		$this->sessionRepository->update($default);
    	
    	$pages = array();
    	$tt_news = array();
    	$news = array();
    	$camaliga = array();
    	if ($linkto_uid > 0) {
    		$pages = $this->getPageLinks($my_c, $my_p, $linkto_uid);
    		if ($exttoo) {
    			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
    				$news = $this->getNewsLinks($my_c, $my_p, $linkto_uid);
    			}
   				if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('camaliga')) {
   					$camaliga = $this->getCamaligaLinks($my_c, $my_p, $linkto_uid);
   				}	
    		}
    	}

    	$this->view->assign('my_c', $my_c);
    	$this->view->assign('my_p', $my_p);
    	$this->view->assign('linksto', $linksto);
    	$this->view->assign('exttoo', $exttoo);
    	$this->view->assign('pages', $pages);
    	$this->view->assign('news', $news);
    	$this->view->assign('tt_news', $tt_news);
    	$this->view->assign('camaliga', $camaliga);
    	$this->view->assign('my_page', $my_page);
    	$this->view->assign('settings', $this->settings);
    }
    
    /**
     * action redirects
     *
     * @return void
     */
    public function redirectsAction()
    {
    	$content = '';
    	$beuser_id = $GLOBALS['BE_USER']->user['uid'];
    	$result = $this->sessionRepository->findByAction('redirects', $beuser_id);
    	if ($result->count() == 0) {
    		$new = TRUE;
    		$default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
    		$default->setAction('redirects');
    		$default->setValue1(0);
    		$default->setValue2(0);
    		$default->setValue3(0);
    		$default->setValue4('');
    		$default->setValue5('0');
    		$default->setValue6('301');
    	} else {
    		$new = FALSE;
    		$default = $result[0];
    	}
    	
    	if ($this->request->hasArgument('method')) {
    		$method = intval($this->request->getArgument('method'));
    		$default->setValue1($method);
    	} else $method = $default->getValue1();
    	if ($this->request->hasArgument('regex')) {
    		$regex = intval($this->request->getArgument('regex'));
    		$default->setValue2($regex);
    	} else $regex = $default->getValue2();
    	if ($this->request->hasArgument('convert')) {
    		$convert = $this->request->getArgument('convert');
    		$default->setValue5($convert);
    	} else $convert = $default->getValue5();
    	if ($this->request->hasArgument('defaultstatuscode')) {
    		$defaultstatuscode = $this->request->getArgument('defaultstatuscode');
    		$default->setValue6($defaultstatuscode);
    	} else $defaultstatuscode = $default->getValue6();
    	if ($this->request->hasArgument('impfile')) {
    		$impfile = $this->request->getArgument('impfile');
    	} else $impfile = '';
    	
    	
    	if ($new) {
    		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    		$backendUserRepository = $objectManager->get(BackendUserRepository::class);
    		/** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
    		$user = $backendUserRepository->findByUid($beuser_id);
    		$default->setBeuser($user);
    		$this->sessionRepository->add($default);
    		$persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
    		$persistenceManager->persistAll();
    	} else {
    		$this->sessionRepository->update($default);
    	}
    	
    	if ($impfile) {
    		$total=0;
    		$success=0;
    		$regexp = ($regex) ? 1 : 0;
    		$treffer = [];
    		$rewrites = [];
    		$filename = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . 'fileadmin/' . $impfile;
    		if (is_file($filename) && file_exists($filename)) {
    			$content .= "This is the result of the file content:<br /><table>\n";
    			$filecontent = fopen($filename,"r");
    			while (!feof($filecontent)) {
    				$row = trim(fgets($filecontent));
    				if ($convert == 'iso') $row = utf8_decode ( $row );
    				if ($convert == 'utf8') $row = utf8_encode ( $row );
    				$row = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($row));	// tab und/oder mehrere Spaces zu einem Space umwandeln
    				$rewrites = explode(' ', $row);
    				preg_match('/R=(\d+)/', $rewrites[3], $treffer);
    				$statuscode = $treffer[1];
    				if (!$statuscode) $statuscode = intval($defaultstatuscode);
    				if ($rewrites[1] && substr($rewrites[1], 0, 2) != '^/') {
    					$rewrites[1] = '^/' . substr($rewrites[1], 1);	// ein / wird am Anfang benötigt
    				}
    				if ($rewrites[1] && $regexp) {
    					$rewrites[1] = '#' . $rewrites[1] . '#';		// TYPO3 will das so
    				}
    				if ($rewrites[1] && $rewrites[2] && (strlen($rewrites[1])>2)) {
    					if ($method) {
	    					if ($this->sessionRepository->addRedirect($rewrites[1], $rewrites[2], $regexp, $statuscode, $beuser_id)) {
	    						$content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#00ff00;"> to </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
	    						$success++;
	    					} else {
	    						$content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#ff0000;"> did not worked </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
	    					}
    					} else {
    						$content .= '<tr><td>' . $rewrites[1] . '</td><td> to </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
    						$success++;
    					}
    				} else {
    					$content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#ff0000;"> ignored </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
    				}
    				$total++;
    			}
    			fclose ($filecontent);
    			$content .= "</table><br />$success/$total lines ";
    			$content .= ($method) ? 'added.' : 'accepted.';
    		} else {
    			$content .= 'Note: file not found!!!';
    		}
    	}
    	$this->view->assign('method', $method);
    	$this->view->assign('regex', $regex);
    	$this->view->assign('defaultstatuscode', $defaultstatuscode);
    	$this->view->assign('convert', $convert);
    	$this->view->assign('impfile', $impfile);
    	$this->view->assign('message', $content);
    }
    
    
    
    
    /**
     * Unzip the source_file in the destination dir
     *
     * @param   string      The path to the ZIP-file.
     * @param   string      The path where the zipfile should be unpacked, if false the directory of the zip-file is used
     *
     * @return  string     Succesful or not
     */
    function unzip($zip_filename, $zip_extract_path) {
    	$result ='';
		try{
			$zip_obj = new \ZipArchive;
			if (file_exists($zip_filename)) {
                $zip_stat = $zip_obj->open($zip_filename);
                if ($zip_stat === TRUE) {
                    $res = $zip_obj->extractTo($zip_extract_path);
                    if ($res === false) {
                       $result = "Error in extracting file on server.";
                    } else {
	                    $zip_obj->close();
						$result = 'The zip-file was unziped to ' . $zip_extract_path;
                    }
                } else {
                    $result = "Error in open file";
                }
            } else {
                $result = "zip file not found for extraction";
            }
		} catch (Exception $e) {
		    $result = $e->getMessage();
		}
    	return $result;
    }
	
	/**
	 * Formats bytes.
	 *
	 * @param integer $size
	 * @param integer $precision
	 * @return string
	 */
	function formatBytes($size, $precision = 2)	{
		$base = log($size) / log(1024);
		$suffixes = array('', 'k', 'M', 'G', 'T');
	
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)] .'B';
	}

	/**
	 * Finde Elemente mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getPageLinks($my_c, $my_p, $linkto_uid) {
		$finalArray = array();
		$where = "(tt.bodytext LIKE '%\"t3://page?uid=".$linkto_uid."\"%' OR tt.header_link='t3://page?uid=".$linkto_uid."' OR tt.header_link LIKE 't3://page?uid=".$linkto_uid." %')";
		// OR tt.image_link=$linkto_uid)";
		if ($my_c==1) $where .= ' AND (tt.deleted=1 OR tt.hidden=1)';
		else if ($my_c==2) $where .= ' AND tt.deleted=0 AND tt.hidden=0';
		if ($my_p==1) $where .= ' AND (pages.deleted=1 OR pages.hidden=1)';
		else if ($my_p==2) $where .= ' AND pages.deleted=0 AND pages.hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tt.uid,tt.pid,tt.deleted AS ttdeleted,tt.hidden AS tthidden,tt.header,tt.sys_language_uid, pages.title,pages.deleted AS pdeleted,pages.hidden AS phidden',
				'tt_content tt, pages',
				'tt.pid=pages.uid AND ' . $where,
				'',
				'tt.pid ASC,tt.sorting',
				'');
		$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rows>0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$finalArray[] = $row;
			}
		}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
    	
    	$where = "(ref.link='t3://page?uid=".$linkto_uid."' OR ref.link LIKE 't3://page?uid=".$linkto_uid." %')";
		if ($my_c==1) $where .= ' AND (tt.deleted=1 OR tt.hidden=1)';
		else if ($my_c==2) $where .= ' AND tt.deleted=0 AND tt.hidden=0';
		if ($my_p==1) $where .= ' AND (pages.deleted=1 OR pages.hidden=1)';
		else if ($my_p==2) $where .= ' AND pages.deleted=0 AND pages.hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tt.uid,tt.pid,tt.deleted AS ttdeleted,tt.hidden AS tthidden,tt.header,tt.sys_language_uid, pages.title,pages.deleted AS pdeleted,pages.hidden AS phidden',
				'tt_content tt, pages, sys_file_reference ref',
				'tt.pid=pages.uid AND tt.uid=ref.uid_foreign AND ref.tablenames="tt_content" AND ' . $where,
				'',
				'tt.pid ASC,tt.sorting',
				'');
		$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rows>0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$finalArray[] = $row;
			}
		}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $finalArray;
	}

	/**
	 * Finde news mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getNewsLinks($my_c, $my_p, $linkto_uid) {
		$finalArray = array();
		$where = "(bodytext LIKE '%\"t3://page?uid=".$linkto_uid."\"%' OR internalurl='t3://page?uid=".$linkto_uid."' OR internalurl LIKE 't3://page?uid=".$linkto_uid." %')";
		if ($my_c==1) $where .= ' AND (deleted=1 OR hidden=1)';
		else if ($my_c==2) $where .= ' AND deleted=0 AND hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,pid,deleted,hidden,title,sys_language_uid',
				'tx_news_domain_model_news',
				$where,
				'',
				'pid ASC,tstamp DESC',
				'');
		$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rows>0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$uid = $row['uid'];
				$finalArray[$uid] = $row;
			}
		}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
    	
		// images links
    	$where = "(ref.link='t3://page?uid=".$linkto_uid."' OR ref.link LIKE 't3://page?uid=".$linkto_uid." %')";
		if ($my_c==1) $where .= ' AND (news.deleted=1 OR news.hidden=1)';
		else if ($my_c==2) $where .= ' AND news.deleted=0 AND news.hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'news.uid,news.pid,news.deleted,news.hidden,news.title,news.sys_language_uid',
				'tx_news_domain_model_news news, sys_file_reference ref',
				'news.uid=ref.uid_foreign AND ref.tablenames="tx_news_domain_model_news" AND ' . $where,
				'',
				'news.pid ASC,news.tstamp DESC',
				'');
		$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rows>0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$uid = $row['uid'];
				$finalArray[$uid] = $row;
			}
		}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
    	
		// related links
    	$where = "(tx_news_domain_model_link.uri='t3://page?uid=".$linkto_uid."' OR tx_news_domain_model_link.uri LIKE 't3://page?uid=".$linkto_uid." %')";
    	if ($my_c==1) $where .= ' AND (news.deleted=1 OR news.hidden=1)';
    	else if ($my_c==2) $where .= ' AND news.deleted=0 AND news.hidden=0';
    	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
    			'news.uid,news.pid,news.deleted,news.hidden,news.title,news.sys_language_uid',
    			'tx_news_domain_model_news news, tx_news_domain_model_link',
    			'news.uid=tx_news_domain_model_link.parent AND ' . $where,
    			'',
    			'news.pid ASC,news.tstamp DESC',
    			'');
    	$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
    	if ($rows>0) {
    		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
    			$uid = $row['uid'];
				$finalArray[$uid] = $row;
    		}
    	}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $finalArray;
	}
	
	/**
	 * Finde Camaliga-Elemente mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getCamaligaLinks($my_c, $my_p, $linkto_uid) {
		$finalArray = array();
		$where = "(longdesc LIKE '%\"t3://page?uid=".$linkto_uid."\"%' OR link='t3://page?uid=".$linkto_uid."' OR link LIKE 't3://page?uid=".$linkto_uid." %')";
		if ($my_c==1) $where .= ' AND (deleted=1 OR hidden=1)';
		else if ($my_c==2) $where .= ' AND deleted=0 AND hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,pid,deleted,hidden,title,sys_language_uid',
				'tx_camaliga_domain_model_content',
				$where,
				'',
				'pid ASC,tstamp DESC',
				'');
		$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rows>0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$finalArray[] = $row;
			}
		}
    	$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $finalArray;
	}
}