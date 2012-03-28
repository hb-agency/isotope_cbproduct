<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2011, Helmut Schottmüller 2009
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Adam Fisher <adam@winanscreative.com>
 * @author     Includes code from survey_ce module from Helmut Schottmüller <typolight@aurealis.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Class for Isotope checkout hooks
 */
class IsotopeCourseBuilder extends Controller
{

	/**
	 * postCheckout Callback function
	 * @param IsotopeOrder
	 * @param array
	 */
	public function postCheckout( IsotopeOrder $objOrder, $arrItemIds )
	{
		$this->import('Database');
		$this->import('FrontendUser', 'User');
		
		$arrProducts = $objOrder->getProducts('', true);
		
		//Need to add this to the member's courses
		$arrCourses = deserialize($this->User->courses, true);
		
		// Get initial course count
		$intCourseCount = count($arrCourses);
				
		foreach( $arrProducts as $objProduct )
		{
			if( FE_USER_LOGGED_IN && $objProduct instanceof CourseProduct && $objProduct->courseid > 0  )
			{				
				if( !in_array($objProduct->courseid, $arrCourses) )
				{
					$arrCourses[] = $objProduct->courseid;
				}														
			}
		}
		
		// Only update if more courses were added
		if (count($arrCourses) > $intCourseCount)
		{
			//Need to add this to the member's courses  - moved outside of foreach by AF
			$this->Database->prepare("UPDATE tl_member SET courses=? WHERE id=?")->execute( serialize($arrCourses), $this->User->id );
			
			$this->log('Updated and assigned member ID:'.$this->User->id.' with course ID:'. $objProduct->courseid , __METHOD__, TL_ACCESS);
		}
	}
	
	/**
	 * mailTokens Callback function
	 * @param array
	 * @param IsotopeProduct
	 * @return array
	 */
	public function mailTokens( $arrSet, IsotopeProduct $objProduct )
	{
		
		if($objProduct->courseid)
		{
			$this->import('Database');
			$arrSet['course'] = $this->Database->prepare("SELECT name FROM tl_cb_course WHERE id=?")->limit(1)->execute($objProduct->courseid)->name;
		}
	
		return $arrSet;
	}
	

}