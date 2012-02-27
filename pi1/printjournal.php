<html>
<head>
<style>
    <!--
	.tx-wecdevo-printBtn {
		text-align: 	center;
	    text-decoration: none;
		font-family: 	 "Trebuchet MS", Tahoma, Georgia, Verdana, Arial,Sans-serif;
		font-size: 		12px;
		font-weight:	normal;
		padding-left:  1px;
		padding-right: 3px;
		margin:  		3px;
	    color: 			#303010;
	    height: 		20px;
	    background-color: #D0D0E0;
	}
	.tx-wecdevo-printBtnHov {
	    background-color: #E8E890;
	 	text-decoration:    none;
	}
    -->
</style>
<script language="Javascript">
<!--
function padout(number) { return (number < 10 && (String(number).charAt(0) != '0')) ? String('0' + number) : String(number); }
function isInt(elm) {
    for (var i = 0; i < elm.length; i++) {
        if (elm.charAt(i) < "0" || elm.charAt(i) > "9") {
            return false;
        }
    }
    return true;
}
function chooseAndClose(whichAction)
{
	today = new Date();
	thisMonth = today.getMonth(); thisMonth++;
	enddate = padout(thisMonth) + padout(today.getDate()) + padout(today.getFullYear());
	switch (whichAction)
	{
		case 1: // TODAY
			startdate = enddate;
			break;
		case 2: // THIS WEEK
			thisWeek = new Date(today.getYear(), today.getMonth(), today.getDate() - today.getDay());
			thisWeekEnd = new Date(today.getYear(), today.getMonth(), today.getDate() - today.getDay() + 7);
			startdate = padout(thisWeek.getMonth()+1)+padout(thisWeek.getDate())+padout(thisWeek.getFullYear());
			enddate   = padout(thisWeekEnd.getMonth()+1)+padout(thisWeekEnd.getDate())+padout(thisWeekEnd.getFullYear());
 			break;
 		case 3: // THIS MONTH
			thisMonth = new Date(today.getYear(), today.getMonth(), 1);
			thisMonthEnd = new Date(today.getYear(), today.getMonth(), 31);
			startdate = padout(thisMonth.getMonth()+1)+padout(thisMonth.getDate())+padout(thisMonth.getFullYear());
			enddate = padout(thisMonthEnd.getMonth()+1)+padout(thisMonthEnd.getDate())+padout(thisMonthEnd.getFullYear());
 			break;
 		case 4: // DATE RANGE
 			// extract dates
			stdate = document.printjournal.fromdate.value;
			sp = 0;
			stMonth = stdate.substr(sp,2); if (!isInt(stMonth)) { stMonth = stdate.substr(sp,1); sp += 2; } else { sp += 3; }
			stDay 	= stdate.substr(sp,2); if (!isInt(stDay)) 	{ stDay = stdate.substr(sp,1); sp += 2; } else { sp += 3; }
			stYear 	= stdate.substr(sp,4); if (!isInt(stYear)) 	{ stYear = stdate.substr(sp,4); }
			startdate = padout(stMonth)+padout(stDay)+stYear;

			endate   = document.printjournal.todate.value;
			sp = 0;
			enMonth = endate.substr(sp,2); if (!isInt(enMonth)) { enMonth = endate.substr(sp,1); sp += 2; } else { sp += 3; }
			enDay 	= endate.substr(sp,2); if (!isInt(enDay)) 	{ enDay = endate.substr(sp,1); sp += 2; } else { sp += 3; }
			enYear 	= endate.substr(sp,4); if (!isInt(enYear))  { enYear = endate.substr(sp,4); }
			enddate = padout(enMonth)+padout(enDay)+enYear;
			//alert("start="+startdate+" end="+enddate);
			break;

 		case 5: // ALL
	 		first = new Date(today.getYear()-5, 1, 1);
			startdate = padout(first.getMonth())+padout(first.getDate())+padout(first.getFullYear());
			now = new Date(today.getYear(), today.getMonth(), today.getDate());
			endddate = padout(now.getMonth())+padout(now.getDate())+padout(now.getFullYear());
 			break;
	}
	opener.doprint(startdate,enddate);
	window.close();
}

-->
</script>
</head>
<body style="margin:0px">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgColor="#F0F0F8">
  <tr>
    <td><div style="border-bottom:2px single #404040;font-weight:bold;background-color:#C0C0C8;padding:6px;text-align:center;">Print Journal Options</div></td>
  </tr>
  <tr>
    <td>
      <FORM NAME="printjournal" method="post" action="<?=$_SERVER['PHP_SELF']?>&doPrint=1">
        <input type="hidden" name="printaction" value="0">
        <input type="hidden" name="printstart" value="0">
        <input type="hidden" name="printend" value="0">
        <p align="center">
          <font size="2">What journal entries would you like to print?
            <br>
            <br>
       	  	<input TYPE="button" class="tx-wecdevo-printBtn" style="width:150px;" name="Today" VALUE="Today" onClick="chooseAndClose(1);" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
       	  	<br>
   	    	<input TYPE="button" class="tx-wecdevo-printBtn" style="width:150px" name="ThisWeek" VALUE="This Week" onClick="chooseAndClose(2);" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
   	    	<br>
          	<input TYPE="button" class="tx-wecdevo-printBtn" style="width:150px" name="ThisMonth" VALUE="This Month" onClick="chooseAndClose(3);" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
          	<br>
          	<input TYPE="button" class="tx-wecdevo-printBtn" style="width:150px" name="All" VALUE="All" onClick="chooseAndClose(5);" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
            <br>
          	Start:<input TYPE="text" NAME="fromdate" size="12" maxlength="12">(date="MM-DD-YY")
          	<br>
        	End:<input TYPE="text" NAME="todate" size="12" maxlength="12">(date="MM-DD-YY")
        	<br>
          	<input TYPE="button"class="tx-wecdevo-printBtn" name="DateRange" VALUE="Date Range" onClick="chooseAndClose(4);" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
          	<br>
          	<br>
          	<input TYPE="button" class="tx-wecdevo-printBtn" VALUE="CANCEL" onClick="window.close();" onMouseOver="this.className='tx-wecdevo-printBtn tx-wecdevo-printBtnHov'" onMouseOut="this.className='tx-wecdevo-printBtn'">
          </font>
      </FORM>
    </td>
  </tr>
</table>
</body>
</html>
