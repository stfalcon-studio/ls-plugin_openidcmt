{assign var="oUser" value=$oComment->getUser()}
{assign var="oVote" value=$oComment->getVote()}

<div id="comment_id_{$oComment->getId()}" class="comment {if $bComments and !$bTalkPage and $oTopic and $oComment->getUserId() == $oTopic->getUserId()}op{/if} {if $oComment->isBad()}bad{/if} {if !$oUserCurrent or ($oUserCurrent and !$oUserCurrent->isAdministrator())}not-admin{/if} {if $oComment->getDelete()} deleted{elseif $oUserCurrent and $oComment->getUserId()==$oUserCurrent->getId()}self{elseif $sDateReadLast<=$oComment->getDate()}new{/if}">
{if !$oComment->getDelete() or $bOneComment or ($oUserCurrent and $oUserCurrent->isAdministrator())}
	<a name="comment{$oComment->getId()}" ></a>
	
	<div class="comment-badge" title="{$aLang.social_author_topic}"></div>
	<a href="{$oUser->getUserWebPath()}"><img src="{$oUser->getProfileAvatarPath(48)}" alt="avatar" class="avatar" /></a>
	
	<ul class="info">
		<li class="username"><a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a></li>
		<li class="date"><a href="{if $oConfig->GetValue('module.comment.nested_per_page')}{router page='comments'}{else}#comment{/if}{$oComment->getId()}">{date_format date=$oComment->getDate() format="j F Y, H:i"}</a></li>
		{if $oComment->getPid()}
			<li class="goto-comment-parent"><a href="#" onclick="ls.comments.goToParentComment({$oComment->getId()},{$oComment->getPid()}); return false;" title="{$aLang.comment_goto_parent}">↑</a></li>
		{/if}
		<li class="goto-comment-child"><a href="#" title="{$aLang.comment_goto_child}">↓</a></li>
		{if $oUserCurrent and !$bNoCommentFavourites}
			<li><a href="#" onclick="return ls.favourite.toggle({$oComment->getId()},this,'comment');" class="favourite {if $oComment->getIsFavourite()}active{/if}"></a></li>
		{/if}
		{hook run='comment_action' comment=$oComment}
		{if $oComment->getTargetType()!='talk'}						
			<li id="vote_area_comment_{$oComment->getId()}" class="voting {if $oComment->getRating()==0}voting-zero{/if} {if $oComment->getRating()>0}positive{elseif $oComment->getRating()<0}negative{/if} {if !$oUserCurrent || $oComment->getUserId()==$oUserCurrent->getId() ||  strtotime($oComment->getDate())<$smarty.now-$oConfig->GetValue('acl.vote.comment.limit_time')}guest{/if}   {if $oVote} voted {if $oVote->getDirection()>0}plus{else}minus{/if}{/if}  ">
				<a href="#" class="minus" onclick="return ls.vote.vote({$oComment->getId()},this,-1,'comment');"></a>
				<a href="#" class="plus" onclick="return ls.vote.vote({$oComment->getId()},this,1,'comment');"></a>
				<span id="vote_total_comment_{$oComment->getId()}" class="total">{$oComment->getRating()}</span>
			</li>
		{/if}
	</ul>
	
	
	<div id="comment_content_id_{$oComment->getId()}" class="content">						
		{$oComment->getText()}
	</div>
		
		
	<div class="comment-actions">
		{if !$oComment->getDelete() and !$bAllowNewComment}<a href="#" onclick="ls.comments.toggleCommentForm({$oComment->getId()}); return false;" class="reply-link">{$aLang.comment_answer}</a>{/if}
		
		<a href="#" title="{$aLang.comment_collapse}/{$aLang.comment_expand}" class="folding" {if $bOneComment || !$bComments}style="display: none;"{/if}>{$aLang.comment_fold}</a>
		
		{if !$oComment->getDelete() and $oUserCurrent and $oUserCurrent->isAdministrator()}
			<a href="#" class="delete" onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_delete}</a>
		{/if}
		{if $oComment->getDelete() and $oUserCurrent and $oUserCurrent->isAdministrator()}   										
			<a href="#" class="repair" onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_repair}</a>
		{/if}
	</div>
{else}				
	{$aLang.comment_was_delete}
{/if}	
</div>

	<div id="comment_preview_{$oComment->getId()}" class="comment-preview" style="display: none;"></div>					
	<div class="reply" id="reply_{$oComment->getId()}" style="display: none;"></div>
