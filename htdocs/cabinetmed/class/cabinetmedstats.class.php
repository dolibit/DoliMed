<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *       \file       htdocs/compta/facture/class/facturestats.class.php
 *       \ingroup    factures
 *       \brief      Fichier de la classe de gestion des stats des factures
 *       \version    $Id: cabinetmedstats.class.php,v 1.2 2011/06/13 22:24:19 eldy Exp $
 */
include_once DOL_DOCUMENT_ROOT . "/core/class/stats.class.php";
include_once DOL_DOCUMENT_ROOT . "/compta/facture/class/facture.class.php";
include_once DOL_DOCUMENT_ROOT . "/fourn/class/fournisseur.facture.class.php";
include_once DOL_DOCUMENT_ROOT . "/lib/date.lib.php";

/**
 *       \class      FactureStats
 *       \brief      Classe permettant la gestion des stats des factures
 */
class CabinetMedStats extends Stats
{
	var $db;

    var $socid;
    var $userid;

    var $table_element;
    var $from;
    var $field;
    var $where;


	/**
	 * Constructor
	 *
	 * @param 	$DB		   Database handler
	 * @param 	$socid	   Id third party
	 * @param 	$mode	   Option
     * @param   $userid    Id user for filter
	 * @return CabinetMedStats
	 */
	function CabinetMedStats($DB, $socid=0, $mode, $userid=0)
	{
		global $conf;

		$this->db = $DB;
        $this->socid = $socid;
        $this->userid = $userid;

		$object=new CabinetmedCons($this->db);
		$this->from = MAIN_DB_PREFIX.$object->table_element;
		$this->field='total';

		$this->where=' 1=1';
		if ($this->socid)
		{
			$this->where.=" AND fk_soc = ".$this->socid;
		}
        if ($this->userid > 0) $this->where.=' AND fk_user_creation = '.$this->userid;
	}


	/**
	 * 	Renvoie le nombre de facture par annee
	 *	@return		array	Array of values
	 */
	function getNbByYear()
	{
		$sql = "SELECT YEAR(datecons) as dm, COUNT(*)";
		$sql.= " FROM ".$this->from;
        $sql.= ($this->where?" WHERE ".$this->where:'');
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		return $this->_getNbByYear($sql);
	}


	/**
	 * 	Renvoie le nombre de facture par mois pour une annee donnee
	 *	@param	year	Year to scan
	 *	@return	array	Array of values
	 */
	function getNbByMonth($year)
	{
		$sql = "SELECT MONTH(datecons) as dm, COUNT(*)";
		$sql.= " FROM ".$this->from;
		$sql.= " WHERE datecons BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
		$sql.= ($this->where?" AND ".$this->where:'');
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		$res=$this->_getNbByMonth($year, $sql);
		//var_dump($res);print '<br>';
		return $res;
	}


	/**
	 * 	Renvoie le montant de facture par mois pour une annee donnee
	 *	@param	year	Year to scan
	 *	@return	array	Array of values
	 */
	function getAmountByMonth($year)
	{
		$sql = "SELECT date_format(datecons,'%m') as dm, ";
		$sql.= " SUM(";
		$sql.=$this->db->ifsql('montant_cheque','montant_cheque','0').'+';
		$sql.=$this->db->ifsql('montant_espece','montant_espece','0').'+';
	    $sql.=$this->db->ifsql('montant_carte','montant_carte','0').'+';
	    $sql.=$this->db->ifsql('montant_tiers','montant_tiers','0').')';
		$sql.= " FROM ".$this->from;
		$sql.= " WHERE date_format(datecons,'%Y') = ".$year;
        $sql.= ($this->where?" AND ".$this->where:'');
        $sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		$res=$this->_getAmountByMonth($year, $sql);
		//var_dump($sql);print '<br>';
		return $res;
	}

	/**
	 *	\brief	Return average amount
	 *	\param	year	Year to scan
	 *	\return	array	Array of values
	 */
	function getAverageByMonth($year)
	{
		$sql = "SELECT date_format(datecons,'%m') as dm, ";
        $sql.= " AVG(";
        $sql.=$this->db->ifsql('montant_cheque','montant_cheque','0').'+';
        $sql.=$this->db->ifsql('montant_espece','montant_espece','0').'+';
        $sql.=$this->db->ifsql('montant_carte','montant_carte','0').'+';
        $sql.=$this->db->ifsql('montant_tiers','montant_tiers','0').')';
		$sql.= " FROM ".$this->from;
        $sql.= " WHERE datecons BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        $sql.= ($this->where?" AND ".$this->where:'');
        $sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		return $this->_getAverageByMonth($year, $sql);
	}

	/**
	 *	\brief	Return nb, total and average
	 *	\return	array	Array of values
	 */
	function getAllByYear()
	{
		$sql = "SELECT date_format(datecons,'%Y') as year, COUNT(*) as nb, ";
        $sql.= " SUM(";
        $sql.=$this->db->ifsql('montant_cheque','montant_cheque','0').'+';
        $sql.=$this->db->ifsql('montant_espece','montant_espece','0').'+';
        $sql.=$this->db->ifsql('montant_carte','montant_carte','0').'+';
        $sql.=$this->db->ifsql('montant_tiers','montant_tiers','0').') as total, ';
        $sql.= " AVG(";
        $sql.=$this->db->ifsql('montant_cheque','montant_cheque','0').'+';
        $sql.=$this->db->ifsql('montant_espece','montant_espece','0').'+';
        $sql.=$this->db->ifsql('montant_carte','montant_carte','0').'+';
        $sql.=$this->db->ifsql('montant_tiers','montant_tiers','0').') as avg';
		$sql.= " FROM ".$this->from;
        $sql.= ($this->where?" WHERE ".$this->where:'');
		$sql.= " GROUP BY year";
        $sql.= $this->db->order('year','DESC');

		return $this->_getAllByYear($sql);
	}
}

?>