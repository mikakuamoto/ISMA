/*
 * This file is NOT a part of Moodle - http://moodle.org/
 *
 * This client for Moodle 2 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */
package client;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.Hashtable;

import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;

/**
 * XML-RPC MOODLE Client
 * You need to download the Apache XML-RPC library http://apache.mirror.aussiehq.net.au//ws/xmlrpc/
 * and add the jar files to your project.
 * 
 * @author Jerome Mouneyrac jerome@moodle.com
 */
public class XmlRpcIsmaClient {

    /**
     * Do a XML-RPC call to Moodle. Result are displayed in the console log.
     * @param args the command line arguments
     * 
     * $event1['courseid'] = '2';
        $event1['name'] = 'aula 1';
        $event1['description'] = 'descriÃ§Ã£o aula 1';
        $event1['timestart'] = '2012;4;20;19;30;0';
        $event2['courseid'] = '2';
        $event2['name'] = 'aula 2';
        $event2['description'] = 'descriÃ§Ã£o aula 2';
        $event2['timestart'] = '2012;4;21;19;30;0';
        $event3['courseid'] = '2';
        $event3['name'] = 'aula 3';
        $event3['description'] = 'descriÃ§Ã£o aula 3';
        $event3['timestart'] = '2012;4;22;19;30;0';
     */
    public static void main(String[] args) throws MalformedURLException, XmlRpcException {

        /// NEED TO BE CHANGED
        String token = "bf59c1d0a252d1dc36a2bc5dd929f63d";
        String domainName = "http://localhost:81";

        /// PARAMETERS - NEED TO BE CHANGED IF YOU CALL A DIFFERENT FUNCTION
        String functionName = "local_wstemplate_hello_world";
        
        Hashtable event1 = new Hashtable();
        event1.put("courseid", "2");
        event1.put("name", "aula 1");
        event1.put("description", "desc aula 1");
        event1.put("timestart", "2012;3;26;19;30;0");
        Hashtable event2 = new Hashtable();
        event2.put("courseid", "2");
        event2.put("name", "aula 2");
        event2.put("description", "desc aula 2");
        event2.put("timestart", "2012;3;27;19;30;0");
        Hashtable event3 = new Hashtable();
        event3.put("courseid", "2");
        event3.put("name", "aula 3");
        event3.put("description", "desc aula 3");
        event3.put("timestart", "2012;3;28;19;30;0");
       
       Object[] calendar = new Object[]{event1, event2, event3};

        /// XML-RPC CALL
        String serverurl = domainName + "/webservice/xmlrpc/server.php" + "?wstoken=" + token + "&wsfunction=" + functionName;
        XmlRpcClientConfigImpl config = new XmlRpcClientConfigImpl();
        config.setServerURL(new URL(serverurl));
        XmlRpcClient client = new XmlRpcClient();
        client.setConfig(config);
        
        Object[] params = new Object[]{calendar};
        //Object[] result = (Object[]) client.execute(functionName, calendar);
        
        Object result = (Object) client.execute(functionName, params);
        
        System.out.println("result: " + (String)result);

        
//        //Display the result in the console log
//        //This piece of code NEED TO BE CHANGED if you call another function
//        System.out.println("An array has been returned. Length is " + result.length);
//        for (int i = 0; i < result.length; i++) {
//                HashMap createduser = (HashMap) result[i];
//                Integer id = (Integer) createduser.get("id");
//                System.out.println("id = " + id);
//                String username = (String) createduser.get("username");
//                System.out.println("username = " + username);
//
//        }
    }
}