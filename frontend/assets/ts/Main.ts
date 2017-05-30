import * as $ from "jquery";
import {EmailList} from "./classes/EmailList";
import {Senders} from "./classes/Senders";
import {Templates} from "./classes/Templates";
$(function(){
	EmailList.initIfNeeded();
	Senders.initIfNeeded();
	Templates.initIfNeeded();
});