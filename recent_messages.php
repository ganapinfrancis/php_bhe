
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['message_id'];
    $del_q = "DELETE FROM private_messages WHERE message_id=$message_id";
    if (mysqli_query($conn, $del_q)) {
        $message = "<div class='alert alert-success'>🗑️ Message deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Failed to delete message: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
}
