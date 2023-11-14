<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 10th
    Updated: 2023 November 11th

****************/

define('DATE_FORMAT', 'Y, M j, G:i:s');


// Question Submit New
if( $_POST 
    && !empty($_POST['question']) 
    && $_SESSION['login_status'] === 'loggedin' 
    && $_GET 
    && !empty($_GET['id']) 
    && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
{
    $new_question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $question_query = 'INSERT INTO ' . QUESTION_TABLE_NAME . ' (object_id, user_id, question_body) VALUES (:id, :user_id, :new_question)';
    $question_statement = $db->prepare($question_query);
    $question_statement->bindValue(':id', $_GET['id']);
    $question_statement->bindValue(':user_id', $_SESSION['login_account']['user_id']);
    $question_statement->bindValue(':new_question', $new_question);
    $question_statement->execute();
}


// Question Delete
if( $_POST 
    && !empty($_POST['delete']) 
    && !empty($_POST['question_id']) 
    && filter_input(INPUT_POST,'question_id',FILTER_VALIDATE_INT)
    && $_SESSION['login_status'] === 'loggedin'
    && $_SESSION['login_account']['user_is_admin']
    && $_GET 
    && !empty($_GET['id']) 
    && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
{
    $question_query = "DELETE FROM ".QUESTION_TABLE_NAME." WHERE question_id=:id LIMIT 1";
    $question_statement = $db->prepare($question_query);
    $question_statement->bindValue(':id', $_POST['question_id']);
    $question_statement->execute();
}


// Answer Submission
if($_POST 
    && !empty($_POST['submit_answer']) 
    && !empty($_POST['question_id']) 
    && filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT)
    && filter_input(INPUT_POST, 'answer_body', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
    && $_SESSION['login_status'] === 'loggedin'
    && $_SESSION['login_account']['user_is_admin'])
{
    $answer_body = filter_input(INPUT_POST, 'answer_body', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $question_query = 'UPDATE ' . QUESTION_TABLE_NAME . ' SET answer_body=:answer_body, answer_timestamp=NOW() WHERE question_id=:question_id LIMIT 1';
    $question_statement = $db->prepare($question_query);
    $question_statement->bindValue(':answer_body', $_POST['answer_body']);
    $question_statement->bindValue(':question_id', $_POST['question_id']);
    $question_statement->execute();

    $answering_question_id = "";
}


// Retreive All Questions
if( $_GET 
    && !empty($_GET['id']) 
    && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
{
    $question_query =
    'SELECT u.user_name, q.question_id, q.question_timestamp, q.question_body, q.answer_body, q.answer_timestamp
    FROM ' . QUESTION_TABLE_NAME . ' q 
        LEFT OUTER JOIN ' . USER_TABLE_NAME . ' u
        ON q.user_id = u.user_id
    WHERE q.object_id = :id
    ORDER BY q.question_timestamp DESC';
    $question_statement = $db->prepare($question_query);
    $question_statement->bindValue(':id', $_GET['id']);
    $question_statement->execute();
}
?>


<!-- Edit/Submit an Answer -->
<?php if(!empty($_POST['edit_answer']) 
        && !empty($_POST['question_id']) 
        && filter_input(INPUT_POST,'question_id',FILTER_VALIDATE_INT) 
        && $_SESSION['login_status'] === 'loggedin' 
        && $_SESSION['login_account']['user_is_admin']): ?>
    <?php while ($question = $question_statement->fetch()): ?>
        <?php if ($question['question_id'] == $_POST['question_id']): ?>
            <section class="answering_question">
                <div class="question_header">
                    <p><?= empty($question['user_name']) ? '[deleted user]' : $question['user_name'] ?></p>
                    <p><?= date(DATE_FORMAT, strtotime($question['question_timestamp'])) ?></p>
                </div>
                <h2><?= $question['question_body'] ?></h2>
                <form method='post' action='#'>
                    <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>" ?>
                    <label for='answer_body'>Enter an Answer:</label>
                    <textarea id='answer_body' name='answer_body'><?= empty($question['answer_body']) ? "" : $question['answer_body'] ?></textarea>
                    <input id="submit_answer" name='submit_answer' type="submit" value="Answer">
                </form>
            </section>
        <?php endif ?>
    <?php endwhile ?>

<!-- Standard Display -->
<?php else: ?>
    <!-- New Question  -->
    <section class="new_question">
        <form method='post' action='#'>
            <label for='question'><b>Submit Your Own Question:</b></label>
            <textarea id='question' name='question'></textarea>
            <input id="submit" name='submit' type="submit" value="Submit">
        </form>

        <!-- Error if user submitted a question without being logged in -->
        <?php if($_POST && !empty($_POST['question']) && $_SESSION['login_status'] !== 'loggedin'): ?>
            <p class='question_error'>You must be logged in before asking a question</p>
        <?php endif ?>
    </section>

    <!-- Display all existing questions -->
    <?php while ($question = $question_statement->fetch()): ?>
        <section class="object_question">
            <div class="question_header">
                <p><?= empty($question['user_name']) ? '[deleted user]' : $question['user_name'] ?></p>
                <p><?= date(DATE_FORMAT, strtotime($question['question_timestamp'])) ?></p>
            </div>
            <h2><?= $question['question_body'] ?></h2>
            <?php if(!empty($question['answer_body'])): ?>
                <p><?= 'Answered on: ' . date(DATE_FORMAT, strtotime($question['answer_timestamp'])) ?></p>
                <h3><?= $question['answer_body'] ?></h3>
            <?php else: ?>
                <p>Unanswered</p>
            <?php endif ?>    

            <!-- Delete and Answer options for admins -->
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <form method='post' action='#'>
                    <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>" ?>
                    <input id="delete" name='delete' type="submit" value="Delete">
                    <input id="edit_answer" name='edit_answer' type="submit" value="Answer">
                </form>
            <?php endif ?>    
        </section>
    <?php endwhile ?>
<?php endif ?> 


