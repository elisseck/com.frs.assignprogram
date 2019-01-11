
<h3>Assign Child to a Program</h3>
<table><thead>
    <tr>
      <th>
        Program Name
      </th>
      <th>
        Hours to Approve
      </th>
      <th>
        Weekly / Monthly
      </th>
    </tr>
    <thead>
  <tbody>
{foreach from=$elementNames item=elementName}
{assign var=etext value=">"|explode:$form.$elementName.label}
{if $etext[1] == 'Hours to Approve</label'}
  <td>
    <div class="content">{$form.$elementName.html}</div>
  </td>
{elseif $etext[1] == 'Weekly / Monthly</label'}
  <td>
    <div class="content">{$form.$elementName.html}</div>
  </td></tr>
{else}
  <tr><td>
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
  </td>
{/if}
{/foreach}
</tbody>
</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script>
  cj("#AssignProgram div.content").attr('style', 'float:left;');
  cj("#AssignProgram div.label").attr('style', 'float: left;padding-right: 5px;');
  cj("input[name=assign_program_contact_id]").add("label[for=assign_program_contact_id]").hide();
</script>
{/literal}
