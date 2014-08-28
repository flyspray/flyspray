<?php
/**
 * Flyspray REST Services
 *
 * api_tasks Class
 *
 * This class provides all of the api functions for task based functions.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray/api
 * @author Steven Tredinnick
 */

class api_tasks
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
     * @param int $taskId
     */
    public function getTaskDetailsBasic($taskId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $taskId
     */
    public function getTaskDetailsExtended($taskId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $projectId
     * @param int $limit
     * @param int $offset
     */
    public function getTasksFromAProject($projectId, $limit, $offset)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function getTasksByDateRange($startDate,$endDate)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $limit
     */
    public function getLastUpdatedTasks($limit)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $type
     */
    public function getTasksByType($type)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $severity
     */
    public function getTasksBySeverity($severity)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $dueVersion
     */
    public function getTasksByDueVersion($dueVersion)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $reportedVersion
     */
    public function getTasksByReportedVersion($reportedVersion)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $category
     */
    public function getTasksByCategory($category)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $status
     */
    public function getTasksByStatus($status)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $percentage
     */
    public function getTasksByPercentageComplete($percentage)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param string $taskType
     * @param string $taskSeverity
     * @param string $taskPriority
     * @param string $taskDueVersion
     * @param string $taskReportedVersion
     * @param string $taskCategory
     * @param string $taskStatus
     * @param string $taskPercentage
     */
    public function getTasksByComplexCriteria($taskType ="*",$taskSeverity ="*",$taskPriority ="*",$taskDueVersion ="*",$taskReportedVersion ="*",$taskCategory ="*",$taskStatus ="*",$taskPercentage ="*")
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $taskId
     */
    public function putUpdateTask($taskId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $taskId
     * @param string $commentText
     */
    public function postNewComment($taskId,$commentText)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }

    /**
     * @param int $projectId
     */
    public function postNewTask($projectId)
    {
        return "Not implemented yet...come back later, or contribute at flyspray.github.com";
    }
} 