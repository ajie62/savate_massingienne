$(function() {

    // Add
    var $addTeamMemberButton = $('#association_addTeamMemberButton');
    var $teamMembersSubformsContainer = $('#association_teamMembers');
    var prototype = $teamMembersSubformsContainer.data('prototype');
    var nbAdded = 0;

    $addTeamMemberButton.on('click', function() {
        nbAdded++;
        prototype = prototype.replace(/__name__/g, 'form-' + nbAdded);
        $teamMembersSubformsContainer.append(prototype);
    });

    // Delete
    $('button.js-delete-team-member').on('click', function() {
       $(this).closest('.team-member').remove();
    });

})



