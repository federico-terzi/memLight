$( document ).ready(function() {
	updateFields($('#question_questionType').val());
});

// When the question type changes, update the shown fields
$('#question_questionType').change(function() {
	updateFields(this.value);
});

// Hide/Show only the required fields for the specified questionType
function updateFields(current)
{
	if (current=="base") {
		$("#widget-container[forField=questionText]").fadeIn()
		$("#widget-container[forField=questionUrl]").fadeOut()
		$("#widget-container[forField=answerText]").fadeIn()
		$("#widget-container[forField=answerUrl]").fadeOut()
	}else if (current=="image_answer") {
		$("#widget-container[forField=questionText]").fadeIn()
		$("#widget-container[forField=questionUrl]").fadeOut()
		$("#widget-container[forField=answerText]").fadeOut()
		$("#widget-container[forField=answerUrl]").fadeIn()
	}else if (current=="custom") {
		$("#widget-container[forField=questionText]").fadeIn()
		$("#widget-container[forField=questionUrl]").fadeIn()
		$("#widget-container[forField=answerText]").fadeIn()
		$("#widget-container[forField=answerUrl]").fadeIn()
	}
}