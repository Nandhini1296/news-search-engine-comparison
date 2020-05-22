import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.*;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

public class PreProcessEdges {

    public static void main(String[] args) throws FileNotFoundException, IOException {
        File dir = new File("../LATIMES/latimes");
        Set<String> edges = new HashSet<String>();

        HashMap<String, String> fileURLMap = new HashMap<String, String>();
        HashMap<String, String> URLfileMap = new HashMap<String, String>();

        BufferedReader br = new BufferedReader(new FileReader("../LATIMES/URLtoHTML_latimes_news.csv"));
        String line = null;

        while((line= br.readLine()) != null){
            String str[] = line.split(",");
            if (str.length > 2){
                System.out.println(str.toString());
            }

            if (fileURLMap.containsKey(str[0])){
                System.out.println(str[0]);
            }
            if (URLfileMap.containsKey(str[1])){
                System.out.println(str[1]);
            }

            fileURLMap.put(str[0],str[1]);
            URLfileMap.put(str[1],str[0]);
        }

        System.out.println(fileURLMap.size());
        System.out.println(URLfileMap.size());
//        System.out.println(fileURLMap.get("0ae2120c-7de8-4c3b-8d24-ef8cd366c411.html"));


        for(File file: dir.listFiles()){
            Document doc = Jsoup.parse(file, "UTF-8", fileURLMap.get(file.getName()));
            Elements links = doc.select("a[href]");
//            Elements pngs = doc.select("[src]");

            for(Element link : links){
                String url = link.attr("abs:href").trim();
                if (URLfileMap.containsKey(url)){
                    edges.add(file.getName() + " " + URLfileMap.get(url));
                }
            }
        }

//        System.out.println(edges);
//        System.out.println();
        System.out.println(edges.size());

        FileWriter writer = new FileWriter("edge_dist_new.txt");

        for (String s: edges){
            writer.write(s+"\n");
        }

        writer.flush();
        writer.close();
    }
}
