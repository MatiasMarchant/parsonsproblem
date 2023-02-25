export const init = (questionid, answerid) => {
    let dragItem = null;
    let draggedOver = null;
    let initX = null;
    let endX = null;
    let leavingColumn = null;
    let enteringColumn = null;
    let content = "";
    let leftColumn = document.getElementById('column' + questionid + '0');
    let rightColumn = document.getElementById('column' + questionid + '1');
    let answerObject = document.getElementById(answerid);

    leftColumn.addEventListener('dragover', dragOverColumn);
    leftColumn.addEventListener('dragenter', dragEnterColumn);
    leftColumn.addEventListener('dragleave', dragLeaveColumn);
    rightColumn.addEventListener('dragover', dragOverColumn);
    rightColumn.addEventListener('dragenter', dragEnterColumn);
    rightColumn.addEventListener('dragleave', dragLeaveColumn);
    let neighborhood = leftColumn.childNodes;
    let answerneighborhood = rightColumn.childNodes;
    neighborhood.forEach(
        function(codeFragment) {
            codeFragment.draggable = true;
            codeFragment.currentIndentation = 0;
            codeFragment.addEventListener('dragstart', dragStart);
            codeFragment.addEventListener('dragend', dragEnd);
            codeFragment.addEventListener('dragover', dragOver);
            if (codeFragment.classList.contains("sortable-choice-parent")) {
                let counter = 0;
                codeFragment.childNodes.forEach(
                    function(choice) {
                        if (counter === 0) {
                            choice.classList.add('chosen-choice');
                        }
                        choice.addEventListener('click', clickChoice);
                        counter++;
                    }
                );
            }
        },
    );

    /**
     * This event triggers when the student clicks a choice in a visually paired choice.
     * It removes the class chosen-choice to all the same codeFragment choices
     * then adds the class chosen-choice to the clicked choice.
     */
    function clickChoice() {
        this.parentNode.childNodes.forEach(
            function(choice) {
                choice.classList.remove('chosen-choice');
            }
        );
        this.classList.add('chosen-choice');
        updateAnswer();
    }

    /**
     * This function triggers whenever a sortable item gets clicked for dragging.
     * It sets the value of initX for future indentation purposes and sets the variable
     * dragItem to the sortable item being dragged.
     * @param {Event} e - Click event that triggers item drag.
     */
    function dragStart(e) {
        initX = e.clientX;
        dragItem = this;
    }

    /**
     * This function triggers whenever dragging stops, it updates the final answer getting uploaded when the student ends the quiz.
     */
    function dragEnd() {
        dragItem = null;
        updateAnswer();
    }

    /**
     * This function updates the answer object considering all the info on the answer column
     */
    function updateAnswer() {
        answerObject.value = "";
        answerneighborhood.forEach(line => {
            if (line.classList.contains("sortable-choice-parent")) {
                line.childNodes.forEach(
                    function(choice) {
                        if (choice.classList.contains("chosen-choice")) {
                            content = choice.innerHTML;
                        }
                    }
                );
            } else {
                content = line.innerHTML;
            }
            let lines = content.split(/\r\n|\r|\n/g);
            lines.forEach(function(contentline) {
                answerObject.value =
                answerObject.value + "    ".repeat(line.currentIndentation) + decodeHtml(contentline) + "\r\n";
            });
            answerObject.value = answerObject.value.slice(0, -2); // Remove last \r\n
            answerObject.value = answerObject.value + '|/';
            content = "";
        });
        answerObject.value = answerObject.value.slice(0, -2); // Remove last '|/'
    }

    /**
     * This function returns an HTML-decoded version of the parameter html
     * @param {String} html - HTML encoded string
     * @returns {String} - HTML decoded string
     */
    function decodeHtml(html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    /**
     * This function triggers whenever a sortable item is being dragged over another sortable item.
     * It sets the variable draggedOver to the sortable item being dragged over.
     * It sets the position of the item being dragged to the position of the item being dragged over,
     * and pushes the item being dragged over up or down depending of the initial position of the item being dragged.
     * @param {Event} e - Click event that triggers item drag.
     */
    function dragOver(e) {
        e.preventDefault();
        draggedOver = e.target;
        // If draggedOver is a visually paired choice, then the choices are the neighborhood and throws errors when calling moveItem
        if (draggedOver.classList.contains("sortable-choice")) {
            draggedOver = draggedOver.parentNode;
        }
        moveItem(draggedOver.parentNode.childNodes);
    }


    /**
     * This function is called whenever an item gets dragged in the valid area (background).
     * It calls the function indentationSpacesOver to manage indentation spaces of the item being dragged.
     * @param {Event} e - Click event that triggers item drag.
     */
    function dragOverColumn(e) {
        e.preventDefault();
        indentationSpacesOver(e);
    }

    /**
     * This function gets called whenever an object gets dragged into one of both columns.
     * When a codeFragment enters another column it gets added as a child of the parent node (column)
     * @param {*} e - Click event that triggers item drag.
     */
    function dragEnterColumn(e) {
        enteringColumn = this;
        if (leavingColumn !== null && leavingColumn !== enteringColumn) { // Esto quiere decir que se salió de una col y entró a otr
            if (this.isSameNode(leftColumn)) {
                leftColumn.appendChild(dragItem);
                initX = e.clientX;
                dragItem.currentIndentation = 0;
                dragItem.style.marginLeft = "0px";
            } else if (this.isSameNode(rightColumn)) {
                rightColumn.appendChild(dragItem);
                initX = e.clientX;
                dragItem.currentIndentation = 0;
                dragItem.style.marginLeft = "0px";
            }
        }
        leavingColumn = null;
        enteringColumn = null;
    }

    /**
     * This function gets called whenever an object gets dragged out of one of both columns.
     * It sets the variable leavingColumn to the column that has a codeFragment leaving.
     * @param {*} e - Click event that triggers item drag.
     */
    function dragLeaveColumn() {
        leavingColumn = this;
    }

    /**
     * This function registers the mouse's X coordinate in order to calculate if the item being dragged
     * should be indented.
     * @param {Event} e - Click event that triggers item drag.
     */
    function indentationSpacesOver(e) {
        endX = e.clientX;
        if (endX >= initX + 50) { // Indentations to the right
            let indentations = Math.floor((endX - initX) / 50);
            dragItem.style.marginLeft = (((dragItem.currentIndentation + indentations) * 50)) + "px";
            dragItem.currentIndentation = dragItem.currentIndentation + indentations;
            initX = endX;
        } else {
            let indentations = Math.floor((Math.abs(initX - endX)) / 50);
            if (dragItem.currentIndentation - indentations <= 0) {
                dragItem.style.marginLeft = "0px";
                dragItem.currentIndentation = 0;
            } else if (indentations >= 1) { // Indentations to the left
                dragItem.style.marginLeft = (((dragItem.currentIndentation - indentations) * 50)) + "px";
                dragItem.currentIndentation = dragItem.currentIndentation - indentations;
                initX = endX;
            }
        }
    }

    /**
     * It sets the position of the item being dragged to the position of the item being dragged over,
     * and pushes the item being dragged over up or down depending of the initial position of the item being dragged.
     * @param {NodeList} neighborhood - NodeList containing draggable codeFragment Nodes
     */
    function moveItem(neighborhood) {
        if (dragItem != draggedOver) {
            let currentpos = 0;
            let droppedpos = 0;
            for (let it = 0; it < neighborhood.length; it++) {
                if (dragItem == neighborhood[it]) { currentpos = it; }
                if (draggedOver == neighborhood[it]) { droppedpos = it; }
            }
            if (currentpos < droppedpos) { // Whenever dragItem item was higher than draggedOver item
                dragItem.parentNode.insertBefore(dragItem, draggedOver.nextSibling);
            } else { // Whenever dragItem item was lower than draggedOver item
                dragItem.parentNode.insertBefore(dragItem, draggedOver);
            }
        }
    }
};