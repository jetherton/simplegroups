<br/>
<div class="slider-holder">
	<form action="">
		<input type="hidden" value="0," name="currentCat" id="currentCat">
		<fieldset>
			<!--<div class="play"><a href="#" id="playTimeline">PLAY</a></div> This is buggy, and not up to snub for my code, plus no one uses it that i know of and it's not worth fixing right now-->
			<label for="startDate"><?php echo Kohana::lang('simplegroups.fro');?></label>
			<select name="startDate" id="startDate"><?php echo $startDate; ?></select>
			<label for="endDate"><?php echo Kohana::lang('simplegroups.to');?></label>
			<select name="endDate" id="endDate"><?php echo $endDate; ?></select>
		</fieldset>
	</form>
</div>
<div id="graph" class="graph-holder"></div>