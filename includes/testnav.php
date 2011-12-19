<!--- Nav links --->
<select name='testlink' id='testlink'>
<option>Testing Links:</option>
<option value="<?php echo DOC_ROOT;?>/activity.php">Activity</a></option>
<!-- Admin-only links -->
<?php if($session->isAdmin()){echo "<option value='".DOC_ROOT."/admin/admin.php'>Administration</a></option>";}
?>
<!--- Teacher-only links --->
<?php if ($session->isInstructor()){ ?>
    <option value="<?php echo DOC_ROOT;?>/instructor/add_class.php">Add a Class</a></option>
    <option value="<?php echo DOC_ROOT;?>/instructor/evaluatee.php">Evaluatee Report</a></option>
    <option value="<?php echo DOC_ROOT;?>/instructor/evaluator.php">Evaluator Report</a></option>
    <option value="<?php echo DOC_ROOT;?>/student/contract.php">Student Contract</a></option>
    <option value="<?php echo DOC_ROOT;?>/instructor/override.php">Overrides</a></option>
    <option value="<?php echo DOC_ROOT;?>/instructor/project.php">New Project</a></option>
    <option value="<?php echo DOC_ROOT;?>/instructor/grades.php">Submit Grades</a></option>
    <!--- Student-only links --->
    <?php }else{     ?>
    <option value="<?php echo DOC_ROOT;?>/student/contract.php">Contract</a></option>
    <option value="<?php echo DOC_ROOT;?>/student/evaluation.php">Do an Evaluation</a></option>
    <option value="<?php echo DOC_ROOT;?>">View Your Evaluations (is this needed?)</a></option>
    <option value="<?php echo DOC_ROOT;?>/student/student-final-report.php">Final Report</a></option>
    <?php }?>
</select>