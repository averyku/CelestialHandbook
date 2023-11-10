<?php

// Submiting a new question
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

// Deleting a question
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

// Selecting the questions for this object's page
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




<?php if($_GET && !empty($_GET['id']) && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)): ?>
    <section class="new_question">
        <form method='post' action='#'>
            <label for='question'>Your Question:</label>
            <input id='question' name='question'>
            <input id="submit" name='submit' type="submit" value="Submit">
        </form>
        <?php if($_POST && !empty($_POST['question']) && $_SESSION['login_status'] !== 'loggedin'): ?>
            <p class='question_error'>You must be logged in before asking a question</p>
        <?php endif ?>
    </section>
    <?php while ($question = $question_statement->fetch()): ?>
        <section class="object_question">
            <p><?= empty($question['user_name']) ? '[deleted user]' : $question['user_name'] ?> - <?= $question['question_timestamp'] ?></p>
            <h2><?= $question['question_body'] ?></h2>
            <h3><?= empty($question['answer_body']) ? "Not Yet Answered" : $question['answer_body'] ?></h3>
            <p><?= 'Answered on: ' . $question['answer_timestamp'] ?></p>
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <form method='post' action='#'>
                    <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>" ?>
                    <input id="delete" name='delete' type="submit" value="Delete">
                </form>
            <?php endif ?>    
        </section>
    <?php endwhile ?>
<?php endif ?>


