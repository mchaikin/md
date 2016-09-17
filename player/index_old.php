<?php
include_once ('connect.php');
include_once ('header.php');
$query = mysql_query ("SELECT *, DATE_FORMAT(`Date`, '%d.%m.%Y') AS 'time' FROM `News` ORDER BY `date` DESC LIMIT 10");
while ($result = mysql_fetch_array($query)) {
$title = $result['Name'];
$about = $result['About'];
$picture = $result['Picture'];
$date = $result['time'];
echo '		<tr>';
echo '			<td class="td_left"></td>';
echo '			<td class="td_center" colspan="4"><h2>'.$title.'</h2></td>';
echo '			<td class="td_right"></td>';
echo '		</tr>';
echo '		<tr>';
echo '			<td class="td_left"></td>';
echo '			<td class="td_about" colspan="4">';
echo '					<div class="corner">';
echo '						<img src="'.$picture.'" alt="'.$title.'"/>';
echo '					</div>';
						$text=trim(mb_substr($about,'0',mb_strrpos(mb_substr($about,'0','600','utf-8'),' ','utf-8'),'utf-8'), '\,');						
echo 					$text;
if (strlen($text) != strlen($about)) {
echo '					...<br><br><p><a href="view.php">Читать дальше...</a></p>';
}
echo '				</td>';
echo '			<td class="td_right"></td>';
echo '		</tr>';
echo '		<tr>';
echo '			<td class="td_left"></td>';
echo '			<td class="td_date" colspan="4">';
echo '					<p>'.$date.'</p>';
echo '			</td>';
echo '			<td class="td_right"></td>';
echo '		</tr>';
echo '		<tr>';
echo '			<td class="td_left"></td>';
echo '			<td class="td_footer" colspan="4">';
echo '					</br></br>';
echo '				</td>';
echo '			<td class="td_right"></td>';
echo '		</tr>';
}
?>
	</table>
</div>
<?php
include_once ('footer.php');
?>
