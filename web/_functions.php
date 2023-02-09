<?php
/**
 * функция не проверяет длину значения, будет true даже с bigint и более.
 */
function isInt (mixed $i): bool
{
	if(is_null($i)
	|| is_bool($i)
	|| is_array($i)
	|| is_object($i)) return false;

	$i = strval($i);
	
	if(preg_match('#^0$#', $i)) return true;

	if(preg_match('#^\-?[1-9][0-9]{0,}$#', $i)) return true;

	return false;
}

/**
 * Расчитываем сколько страниц допустимо
 *
 * @param integer $maxOnePage количество записей на одну страницу.
 * @param integer $countRecords общее количество записей.
 * @return integer количество страниц
 */
function calcMaxPages (int $maxOnePage, int $countRecords):int
{
	$r = intval( ceil( intval( $countRecords ) / $maxOnePage  ) );
	return $r === 0 ? 1 : $r;
}

/**
 * расчитываем offset
 *
 * @param integer $currentNumPage номер текущей страницы
 * @param integer $maxPage общее колисчество страниц
 * @param integer $maxOnePage количество записей на одну страницу
 * @return integer offset
 */
function calcOffset (int $currentNumPage, int $maxPages, int $maxOnePage):int
{
	$offset = ($currentNumPage > $maxPages) ? $maxPages : $currentNumPage;
	$offset = ($offset * $maxOnePage) - $maxOnePage;
	return ($offset < 0) ? 0 : $offset;
}