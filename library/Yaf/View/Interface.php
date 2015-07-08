<?php

/**
 * Yaf View Interface
 */
interface Yaf_View_Interface
{
    /**
     * Assign variables to the view script via differing strategies.
     *
     * Suggested implementation is to allow setting a specific key to the
     * specified value, OR passing an array of key => value pairs to set en
     * masse.
     *
     * @param string|array $name
     *            The assignment strategy to use
     *            (key or array of key => value pairs)
     * @param mixed $value
     *            (Optional) If assigning a named variable, use this
     *            as the value.
     *            
     * @return void
     */
    public function assign ($name, $value = null);

    public function getAssign ($name);

    /**
     * Processes a view script and returns the output.
     *
     * @param string $tpl
     *            The script name to process.
     * @param array $tplVars
     *            The script variables.
     *            
     * @return string The script output.
     */
    public function render ($tpl, $tplVars = array());

    /**
     * Processes a view script and displays the output.
     *
     * @param string $tpl
     *            The script name to process.
     * @param array $tplVars
     *            The script variables.
     *            
     * @return void
     */
    public function display ($tpl, $tplVars = array());
}
