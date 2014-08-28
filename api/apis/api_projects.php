<?php
/**
 * Flyspray REST Services
 *
 * api_projects Class
 *
 * This class provides all of the api functions for project based functions.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray/api
 * @author Steven Tredinnick
 */
class api_Projects
{
    /** @var PDO $_db */
    private $_db = null;

    /**
     *
     */
    public function __construct()
    {
        $this->_db = pdoDB::getConnection();
    }

    /**
     * Lists basic details of all projects listed in the database.
     *
     * Note this only returns projects that are public or where the user is a member of the project.
     * @return array
     *
     * todo: Need to add access control to this section.
     */
    public function getProjects()
    {
        $query = $this->_db->prepare("SELECT `project_id`,`project_title`,`project_is_active`,`intro_message` FROM `flyspray_projects`");
        $query->execute();
        return($query->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Lists extended details of all projects in the database.
     *
     * Note this only returns projects that are public or where the user is a member of the project.
     * @return array
     *
     * todo: Need to add access control to this section.
     */
    public function getProjectsExtended()
    {
        $query = $this->_db->prepare("SELECT * FROM `flyspray_projects`");
        $query->execute();
        return($query->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Gets Basic details of a project by its project id.
     *
     * Invoking this will return the Project ID, Project Title, Project Intro Message and if the project is active from
     * the database filtered by the id of the project that you want to return.
     *
     * @param int $projectId The ID of the project to return
     * @return array
     *
     * todo: Need to add access control to this section.
     */
    public function getProject($projectId)
    {
        $query = $this->_db->prepare("SELECT `project_id`,`project_title`,`project_is_active`,`intro_message` FROM `flyspray_projects` WHERE project_id=:project_id");
        $query->bindParam('project_id',$projectId,PDO::PARAM_INT);
        $query->execute();
        return($query->fetchAll(PDO::FETCH_ASSOC));
    }


    /**
     * Gets all data fields for a project by its project id.
     *
     * Invoking this will return all of the data fields that are available for a project from the database, filtered by
     * the project id of the project that you want to return.
     *
     * @access protected
     * @class  AccessControl {@requires user}
     * @param int $projectId The ID of the project to return
     * @return array
     *
     * todo: Need to add access control to this section.
     */
    public function getProjectExtended($projectId)
    {
        $query = $this->_db->prepare("SELECT * FROM `flyspray_projects` WHERE project_id=:project_id");
        $query->bindParam('project_id',$projectId,PDO::PARAM_INT);
        $query->execute();
        return($query->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Gets all groups that are associated to a project.
     *
     * Invoking this will return a list of groups that are associated to a project., filtered by
     * the project id of the project that you want to return.
     *
     * @access protected
     * @class  AccessControl {@requires user}
     * @param int $projectId The ID of the project to return
     * @return array
     *
     * todo: Need to add access control to this section.
     * todo: Need to code the api for this section.
     */
    public function getProjectGroups($projectId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * Gets all data fields for a project by its project id.
     *
     * Invoking this will return all project members and the groups they are associated to, filtered by
     * the project id of the project that you want to return.
     *
     * @access protected
     * @class  AccessControl {@requires user}
     * @param int $projectId The ID of the project to return
     * @return array
     *
     * todo: Need to add access control to this section.
     * todo: Need to code the api for this section.
     */
    public function getProjectMembers($projectId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    public function postNewProject($projectName)
    {

    }

    public function putUpdateProject($projectId)
    {

    }
}